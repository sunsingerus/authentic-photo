<?php

namespace App\Model;

use App\Common\Helper;

/**
 * Class MediaFile
 *
 * @property integer	    $id
 * @property string	        $file
 * @property string	        $name
 * @property string	        $mime
 * @property string	        $size
 * @property integer	    $created_by
 * @property integer	    $updated_by
 * @property \Carbon\Carbon	$created_at
 * @property \Carbon\Carbon	$updated_at
 *
 * @package App\Model
 */
final class MediaFile extends BaseModel
{
    protected $table = 'media_files';

    protected $fillable = [
        'file',
        'name',
        'mime',
        'size',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    private static $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
    ];

    private static $allowedExt = [
        'jpg',
        'png',
        'jpeg',
    ];

    /**
     * Create new MediaFile instance and save it
     *
     * @param \Slim\Http\UploadedFile $file
     * @param string                  $uploadPath
     *
     * @return MediaFile|null
     * @throws \Exception
     */
    public static function create($file, $uploadPath)
    {
        if (!is_dir($uploadPath) || !is_writable($uploadPath)) {
            throw new \Exception(sprintf('The directory `%s` is not good', $uploadPath));
        }

        $uploadFileName = $file->getClientFilename();
        $uploadMimeType = $file->getClientMediaType();
        $fileName       = basename($uploadFileName);
        $pieces         = explode('.', $fileName);
        $ext            = end($pieces);

        if (
            !in_array($uploadMimeType, self::$allowedMimeTypes)
            || !in_array($ext, self::$allowedExt)
        ) {
            throw new \Exception('This type of files is not supported');
        }

        $mediaFile = new self([
            'file' => Helper::generateRandomString().'.'.$ext,
            'name' => $fileName,
            'mime' => $uploadMimeType,
            'size' => $file->getSize(),
        ]);

        $file->moveTo($uploadPath.'/'.$mediaFile->file);

        if (!$mediaFile->save()) {
            return null;
        }

        return $mediaFile;
    }
}
