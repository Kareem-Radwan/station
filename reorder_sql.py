#!/usr/bin/env python3
"""
reorder_sql.py
--------------
Reads a MySQL dump file (produced by cbpms or similar Laravel apps) and
rewrites it with tables in the correct FK-dependency order so the file can
be imported without foreign-key errors.

Usage:
    python reorder_sql.py input.sql [output.sql]

If output.sql is omitted the result is written to
    <input_stem>_reordered.sql
"""

import re
import sys
from collections import defaultdict, deque
from pathlib import Path


# ── helpers ──────────────────────────────────────────────────────────────────

def parse_blocks(sql_text: str) -> dict[str, dict]:
    """
    Split the dump into per-table blocks.
    Each block contains:
        - header : comment line(s) before the table
        - drop   : DROP TABLE IF EXISTS statement
        - create : CREATE TABLE statement
        - inserts: list of INSERT lines
    Tables are keyed by their lowercase name.
    Returns an ordered dict (insertion order = original file order).
    """
    blocks: dict[str, dict] = {}
    preamble_lines: list[str] = []          # lines before the first table block
    current_table: str | None = None
    current_block: dict = {}
    in_create: bool = False
    create_buffer: list[str] = []

    lines = sql_text.splitlines(keepends=True)
    i = 0

    def flush_current():
        nonlocal current_table, current_block
        if current_table:
            blocks[current_table] = current_block
        current_table = None
        current_block = {}

    while i < len(lines):
        line = lines[i]

        # ---- detect "-- Table: <name>" comment ----
        m_table_comment = re.match(r"--\s+Table:\s+(\S+)", line)
        if m_table_comment:
            flush_current()
            tname = m_table_comment.group(1).strip("`").lower()
            current_table = tname
            current_block = {
                "header": line,
                "drop": "",
                "create": "",
                "inserts": [],
                "data_header": "",
            }
            i += 1
            continue

        if current_table is None:
            preamble_lines.append(line)
            i += 1
            continue

        # ---- DROP TABLE ----
        if re.match(r"DROP\s+TABLE", line, re.IGNORECASE):
            current_block["drop"] = line
            i += 1
            continue

        # ---- CREATE TABLE (may span multiple lines) ----
        if re.match(r"CREATE\s+TABLE", line, re.IGNORECASE):
            in_create = True
            create_buffer = [line]
            i += 1
            continue

        if in_create:
            create_buffer.append(line)
            # END of CREATE TABLE: line starts with ) and contains ENGINE=
            if re.match(r"\)\s*ENGINE\s*=", line, re.IGNORECASE):
                in_create = False
                current_block["create"] = "".join(create_buffer)
            i += 1
            continue

        # ---- "-- Data for table:" comment ----
        m_data = re.match(r"--\s+Data\s+for\s+table:", line, re.IGNORECASE)
        if m_data:
            current_block["data_header"] = line
            i += 1
            continue

        # ---- INSERT statements ----
        if re.match(r"INSERT\s+INTO", line, re.IGNORECASE):
            current_block["inserts"].append(line)
            i += 1
            continue

        # blank lines / other comments inside a block — skip silently
        i += 1

    flush_current()
    return preamble_lines, blocks


def extract_fk_deps(create_sql: str, table_name: str) -> list[str]:
    """Return list of tables that `table_name` references via FOREIGN KEY."""
    refs = re.findall(
        r"REFERENCES\s+`?(\w+)`?\s*\(",
        create_sql,
        re.IGNORECASE,
    )
    # Exclude self-references
    return [r.lower() for r in refs if r.lower() != table_name.lower()]


def topological_sort(table_names: list[str], dep_map: dict[str, list[str]]) -> list[str]:
    """
    Kahn's algorithm topological sort.
    Tables not present in dep_map are treated as having no deps.
    Unknown dependencies (tables not in table_names) are ignored gracefully.
    """
    known = set(table_names)
    in_degree: dict[str, int] = {t: 0 for t in table_names}
    adj: dict[str, list[str]] = defaultdict(list)

    for table, deps in dep_map.items():
        for dep in deps:
            if dep in known and dep != table:
                adj[dep].append(table)
                in_degree[table] += 1

    queue = deque(sorted(t for t, d in in_degree.items() if d == 0))
    order: list[str] = []

    while queue:
        node = queue.popleft()
        order.append(node)
        for neighbor in sorted(adj[node]):   # sort for determinism
            in_degree[neighbor] -= 1
            if in_degree[neighbor] == 0:
                queue.append(neighbor)

    # If any tables remain (cycle or unknown dep), append them at the end
    remaining = [t for t in table_names if t not in order]
    if remaining:
        print(f"⚠  Warning: could not resolve order for: {remaining}", file=sys.stderr)
        print("   They will be appended at the end.", file=sys.stderr)
        order.extend(remaining)

    return order


def rebuild_sql(preamble: list[str], blocks: dict[str, dict], order: list[str]) -> str:
    parts: list[str] = []

    # Preamble (header comment, SET statements, etc.)
    parts.append("".join(preamble))

    for tname in order:
        block = blocks.get(tname)
        if not block:
            continue
        parts.append(block["header"])
        if block["drop"]:
            parts.append(block["drop"])
        if block["create"]:
            parts.append(block["create"])
        if block["inserts"]:
            if block["data_header"]:
                parts.append(block["data_header"])
            parts.extend(block["inserts"])
        parts.append("\n")   # blank line between tables

    return "\n".join(parts)


# ── main ─────────────────────────────────────────────────────────────────────

def main():
    if len(sys.argv) < 2:
        print("Usage: python reorder_sql.py input.sql [output.sql]")
        sys.exit(1)

    input_path = Path(sys.argv[1])
    if not input_path.exists():
        print(f"Error: file not found: {input_path}")
        sys.exit(1)

    if len(sys.argv) >= 3:
        output_path = Path(sys.argv[2])
    else:
        output_path = input_path.with_stem(input_path.stem + "_reordered")

    sql_text = input_path.read_text(encoding="utf-8")

    print(f"📂 Parsing: {input_path}")
    preamble, blocks = parse_blocks(sql_text)
    print(f"   Found {len(blocks)} table blocks: {', '.join(blocks.keys())}")

    # Build dependency map from CREATE TABLE FK references
    dep_map: dict[str, list[str]] = {}
    for tname, block in blocks.items():
        dep_map[tname] = extract_fk_deps(block["create"], tname)

    print("\n🔗 Dependency map:")
    for t, deps in sorted(dep_map.items()):
        if deps:
            print(f"   {t} → {deps}")

    order = topological_sort(list(blocks.keys()), dep_map)

    print("\n✅ Resolved table order:")
    for i, t in enumerate(order, 1):
        print(f"   {i:2d}. {t}")

    out_sql = rebuild_sql(preamble, blocks, order)
    output_path.write_text(out_sql, encoding="utf-8")
    print(f"\n💾 Written: {output_path}")


if __name__ == "__main__":
    main()
