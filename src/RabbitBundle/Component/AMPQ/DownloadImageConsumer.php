<?php

/**
 * Created by PhpStorm.
 * User: oussaka
 * Date: 19/11/2017
 * Time: 14:25
 */
namespace RabbitBundle\Component\AMPQ;

use \Curl\Curl;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class DownloadImageConsumer implements ConsumerInterface
{
    /**
     * @var Curl $curl
     */
    protected $curl;

    // Folders
    protected $createFolderMod = 0755;
    protected $logger;

    /**
     *  Main constructor
     *
     * @param (Curl) $curl                 Curl
     * @param (array) $options             Array of options
     *
     * @return (void)
     */
    public function __construct(Curl $curl, $options = array(), Logger $logger)
    {
        // Initialize
        $this->curl = $curl;
        // Initialize options
        $this->createFolderMod = isset($options['createFolderMod']) ? $options['createFolderMod'] : $this->createFolderMod;
        $this->logger = $logger;
    }

    /**
     *  Main execute method
     *  Execute actiosn for a given message
     *
     * @param (AMQPMessage) $msg       An instance of `PhpAmqpLib\Message\AMQPMessage` with the $msg->body being the data sent over RabbitMQ.
     *
     * @return (boolean) Execution status (true if everything's of, false if message should be re-queued)
     */
    public function execute(AMQPMessage $msg)
    {
        // Initialize
        $imageToDownload = unserialize($msg->body);

        // Download image
        $newFilePath = $this->downloadImageTo($imageToDownload['url'], $imageToDownload['savePath']);
        if (!$newFilePath) {
            // Image should be downloaded again
            return false;
        }

        // Update saved html file
        $savedHtmlFileContent = file_get_contents($imageToDownload['savedHtmlFile']);

        // Update images paths
        $savedHtmlFileContent = str_replace($imageToDownload['url'], $newFilePath, $savedHtmlFileContent);

        // Save file
        return file_put_contents($imageToDownload['savedHtmlFile'], $savedHtmlFileContent);
    }

    /**
     *  Download an image to a given path
     *
     * @param (string) $downloadImagePath          Download image path
     * @param (string) $saveImagePath              Save image path
     *
     * @return (boolean) Download status (or true if file already exists)
     */
    protected function downloadImageTo($downloadImagePath, $saveImagePath)
    {
        // Initialize
        $saveImageFolder = pathinfo($saveImagePath, PATHINFO_DIRNAME);
        // prevent error when filename is not sanitize!
        $saveImagePathSanitize = str_replace(pathinfo($saveImagePath, PATHINFO_FILENAME), md5($saveImagePath), $saveImagePath);

        $saveStatus = false;

        // Check if image already exists
        if (file_exists($saveImagePathSanitize)) {
            echo 'File "' . $saveImagePath . '" already exists' . "\n";
            return true;
        }

        // Check if folder already exists
        if (!is_dir($saveImageFolder)) {
            // Initialize
            $createFolderMod = is_int($this->createFolderMod) ? $this->createFolderMod : intval($this->createFolderMod);

            // Create folder
            mkdir($saveImageFolder, $createFolderMod, true);
            echo 'Folder "' . $saveImageFolder . '" has been created.' . "\n";
        }

        // Download image
        try {
            // Log download status
            echo 'Begin download of "' . $downloadImagePath . '".' . "\n";

            // Get image content
            $imageContent = $this->curl->get($downloadImagePath);
            // Check content
            if (!$imageContent) {
                throw new \Exception('Error downloading file "' . $downloadImagePath . '" : returns a void content or a 404 page.', 1);
                return false;
            }

            $saveStatus = file_put_contents($saveImagePathSanitize, $imageContent);

            // Log info
            echo 'Image "' . $saveImagePath . '" has been successfully downloaded!' . "\n";

        } catch (\Exception $e) {
            // Log error
            $this->logger->error($e->getMessage());
            echo '#ERROR# Image "' . $downloadImagePath . '" was not downloaded! ' . "\n";
        }

        // Return new filename
        return $saveImagePathSanitize;
    }

}
