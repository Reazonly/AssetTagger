<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\SubCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Illuminate\Support\Str;

class SubCategoryImport implements ToModel, WithHeadingRow, WithUpserts
{
    protected $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }
    
    private function findValueByAliases(array $row, array $aliases): ?string
    {
        foreach ($aliases as $alias) {
            if (isset($row[$alias]) && !empty($row[$alias])) {
                return $row[$alias];
            }
        }
        return null;
    }

   
    private function processSpecFieldsFromRow(array $row): array
    {
        $specFields = [];
      
        for ($i = 1; $i <= 10; $i++) {
            $nameKey = 'spek_' . $i;
            $typeKey = 'tipe_input_' . $i;

          
            if (isset($row[$nameKey]) && !empty($row[$nameKey])) {
                
               
                $fieldName = $row[$nameKey];
                
             
                $fieldType = strtolower(trim($row[$typeKey] ?? 'text'));
                if (!in_array($fieldType, ['text', 'number', 'textarea'])) {
                    $fieldType = 'text'; 
                }

                $specFields[] = [
                    'name' => $fieldName,
                    'type' => $fieldType,
                ];
            }
        }
        return $specFields;
    }

    public function model(array $row)
    {
        $normalizedRow = $this->normalizeRowKeys($row);

        $name = $this->findValueByAliases($normalizedRow, ['nama_sub_kategori', 'sub_kategori', 'nama']);
        $inputType = $this->findValueByAliases($normalizedRow, ['tipe_input', 'input']) ?? 'none';
        
        if (!$name) {
            return null; 
        }

        $formattedInputType = strtolower(str_replace(' ', '_', $inputType));
        if (!in_array($formattedInputType, ['merk', 'tipe', 'merk_dan_tipe', 'none'])) {
            $formattedInputType = 'none';
        }

      
        $specFields = $this->processSpecFieldsFromRow($normalizedRow);
       
        return new SubCategory([
            'category_id' => $this->category->id,
            'name'        => $name,
            'slug'        => Str::slug($name),
            'input_type'  => $formattedInputType,
            'spec_fields' => $specFields,
        ]);
    }

    public function uniqueBy()
    {
        return 'name';
    }

    private function normalizeRowKeys(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $newKey = Str::snake(strtolower($key));
            $normalized[$newKey] = $value;
        }
        return $normalized;
    }
}
