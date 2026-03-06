<?php
namespace App\Helper;

use App\Models\AssetDocument;
use Illuminate\Http\Request;

class AssetDocumentHelper
{
    public static function saveFiles(Request $request, int $assetId, int $typeId, string $type = 'assets'): array
    {
        $documentIds = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $originalName = str_replace(' ', '_', $file->getClientOriginalName());
                $fileName = uniqid() . '_' . $originalName;
                $filePath = 'finance/assets_files/' . $fileName;
                $file->move(public_path('finance/assets_files'), $fileName);

                $document = AssetDocument::create([
                    'asset_id'       => $assetId,
                    'type_id'       => $typeId,
                    'name'           => $fileName,
                    'file'           => $filePath,
                    'file_extention' => $file->getClientOriginalExtension(),
                    'type'           => $type,
                    'is_active'      => true,
                    'created_by'     => auth('employee')->id(),
                    'date'           => now(),
                ]);

                $documentIds[] = $document->id;
            }
        }

        return $documentIds;
    }
}
