<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseBackupController extends Controller
{
    public function downloadSql()
    {
        try {
            // Get all tables
            $tables = DB::select("SHOW TABLES");

            $databaseName = DB::getDatabaseName();
            $tableKey = 'Tables_in_' . $databaseName;            
            $sqlContent = "-- CBPMS Database Backup\n";
            $sqlContent .= "-- Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";
            
            // Export each table
            foreach ($tables as $table) {
                $tableName = $table->$tableKey;                
                // Get CREATE TABLE statement
                $createTableResult = DB::select("SHOW CREATE TABLE `$tableName`");
                if (!empty($createTableResult)) {
                    $sqlContent .= "-- Table: {$tableName}\n";
                    $sqlContent .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                    $sqlContent .= $createTableResult[0]->{'Create Table'} . ";\n\n";
                }
                
                // Get table data
                $rows = DB::table($tableName)->get();
                
                if ($rows->count() > 0) {
                    $sqlContent .= "-- Data for table: {$tableName}\n";
                    
                    foreach ($rows as $row) {
                        $columns = array_keys((array)$row);
                        $values = array_map(function($value) {
                            if (is_null($value)) {
                                return 'NULL';
                            }
                            return "'" . str_replace("'", "''", $value) . "'";
                        }, array_values((array)$row));
                        
                        $sqlContent .= "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    
                    $sqlContent .= "\n";
                }
            }
            
            // Generate filename
            $filename = 'cbpms_backup_' . now()->format('Y-m-d_His') . '.sql';
            
            // Return SQL file as download
            return response()->streamDownload(function() use ($sqlContent) {
                echo $sqlContent;
            }, $filename, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
            
        } catch (\Exception $e) {
            return back()->with('error', 'فشل في تخزين قاعدة البيانات: ' . $e->getMessage());
        }
    }
}
