from pathlib import Path

# Laravel project root (current directory)
ROOT = Path.cwd()

# Folders to include
FOLDERS = [
    "app/Models",
    "app/Services",
    "app/Http/Controllers",
    "database/migrations",
    "database/seeders",
]

OUTPUT_FILE = ROOT / "laravel_project_source.txt"

# File extensions to include
EXTENSIONS = {".php"}

with open(OUTPUT_FILE, "w", encoding="utf-8") as out:
    out.write("=" * 100 + "\n")
    out.write("LARAVEL PROJECT SOURCE EXPORT\n")
    out.write("=" * 100 + "\n\n")

    for folder in FOLDERS:
        folder_path = ROOT / folder

        if not folder_path.exists():
            continue

        out.write("\n")
        out.write("#" * 100 + "\n")
        out.write(f"FOLDER: {folder}\n")
        out.write("#" * 100 + "\n\n")

        for file in sorted(folder_path.rglob("*")):
            if file.is_file() and file.suffix in EXTENSIONS:
                relative = file.relative_to(ROOT)

                out.write("=" * 100 + "\n")
                out.write(f"FILE: {relative}\n")
                out.write("=" * 100 + "\n\n")

                try:
                    content = file.read_text(encoding="utf-8")
                except UnicodeDecodeError:
                    content = file.read_text(encoding="latin-1")

                out.write(content)
                out.write("\n\n\n")

print(f"Done! Output written to:\n{OUTPUT_FILE}")