<?php
/**
 * File uploader, provides function to upload files to folder on a website.
 *
 */
class FileUploader
{
    private $folderPath;
    private $maxFileSize;

    /**
     * Constructor
     *
     * Initiates the datbase.
     *
     * @param Database $db the database object.
     */
    public function __construct($folderPath, $maxFileSize)
    {
        $this->folderPath = $folderPath;
        $this->maxFileSize = $maxFileSize;
    }

    /**
     * Uploads imgage to folder.
     *
     * Checks if the extension is jpg, jpeg, png or gif and the image does not
     * exceeds a specified maximum size. Returns the filepath, image name
     * included, if the upload of the image is successful, otherwise exceptions
     * is thrown.
     *
     * @throws UnexpectedValueException if file extension is not allowed or moving
     *                                  of image from temporary folder to destination
     *                                  folder fails.
     * @throws OutOfRangeException      if image size exceeds specified maximum size.
     *
     * @param  [] $file the file data array containing information about the image.
     *
     * @return string       the filepath if successful upload, otherwise null.
     */
    public function uploadImage($file)
    {
        $filePath = null;
        if (isset($file) && !empty($file['name'])) {
            $fileName = $file['name'];
            $fileTmp = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];

            $fileExtension = $this->getFileExtenstionFromFileName($fileName);

            if (!$this->isFileExtensionImage($fileExtension)) {
                throw new UnexpectedValueException('Filnamnstillägget är inte jpg, jpeg, png eller gif');
            }

            if ($fileError !== 0) {
                $message = $this->getFileUploadErrors($fileError);
                throw new UnexpectedValueException($message);
            }

            if ($fileSize > $this->maxFileSize) {
                throw new OutOfRangeException('Filstorleken överstiger definerad maxstorlek');
            }

            $fileDestination = $this->folderPath . $fileName;
            if (move_uploaded_file($fileTmp, $fileDestination)) {
                $filePath = $fileDestination;
            } else {
                throw new UnexpectedValueException('Problem med flytt av fil från tillfällig katalog');
            }
        } else {
            throw new UnexpectedValueException('Bild saknas!');
        }

        return $filePath;
    }

    /**
     * Helper function to get the file extension from file name.
     *
     * Gets the file extension from the file name.
     *
     * @param  string $fileName the file name with extension.
     *
     * @return string the file extension in form of lowercase.
     */
    private function getFileExtenstionFromFileName($fileName)
    {
        $fileExtension = explode(".", $fileName);
        $fileExtension = strtolower(end($fileExtension));

        return $fileExtension;
    }

    /**
     * Helper function to check if file extension belongs to an image.
     *
     * Checks if the file extension is jpg, jpeg, png or gif.
     *
     * @param  string  $fileExtension the file extension in form of lowercase.
     *
     * @return boolean true if the extension is supported as an image, false otherwise.
     */
    private function isFileExtensionImage($fileExtension)
    {
        $allowedImgExt = array('jpg', 'jpeg', 'png', 'gif');

        return in_array($fileExtension, $allowedImgExt);
    }

    /**
     * Helper function to translate file array error codes.
     *
     * Translates file array error codes, which PHP can return at file upload
     * PHP.
     *
     * @param  int $fileError the error code in the file array at PHP file upload.
     *
     * @return string the translation of the error code.
     */
    private function getFileUploadErrors($fileError)
    {
        switch ($fileError) {
            case '1':
                $errorMessage = "Filstorleken överstiger värdet i php.ini";
                break;

            case '2':
                $errorMessage = "Filstorleken överstiger MAX_FILE_SIZE specifierat i HTML form";
                break;

            case '3':
                $errorMessage = "Fil endast delvis uppladdad";
                break;

            case '4':
                $errorMessage = "Filen kunde inte laddas upp";
                break;

            case '6':
                $errorMessage = "Temporär katalog saknas";
                break;

            case '7':
                $errorMessage = "Fil kunde inte skrivas till disk";
                break;

            case '8':
                $errorMessage = "Filtillägg stoppade uppladdning av fil. Kontrollera filtillägget";
                break;

            default:
                $errorMessage = "Okänt fel";
                break;
        }

        return $errorMessage;
    }
}
