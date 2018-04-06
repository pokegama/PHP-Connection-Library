<?php
/**
 * TestFtpClient.php
 *
 * lib/TestFtpClient.php
 *
 * PHP-FTP-Client
 */

/**
 * class TestFtpClient
 *
 * Usesd to test FtpClient
 */
class TestFtpClient {
    protected $ftpClient = null;

    /**
     * Instantiate an TestFtpClient
     *
     * @param void
     *
     * @return void
     */
    public function __construct(FtpClient $ftpClient)
    {
        // This class requires an instance of FtpClient
        $this->ftpClient = $ftpClient;
    }

    public function testAuthNormalSecure ($hostname, $hostport, $username, $password)
    {
        $this->ftpClient->secure = TRUE;
        print("Connecting to FTP server..\r\n");
        if($this->ftpClient->connect($hostname, $hostport))
        {
            print("Succussfully connected to " . $hostname . "\r\n");
    
        }
        else
        {
            print("Failed to connect to " . $hostname . "\r\n");
            die(1);
        }
    
        if($this->ftpClient->authenticateNormal($username, $password))
        {
            print("Authentication successful.\r\n");
           
        }
        else
        {
            print("Authentication failed.\r\n");
            die(1);
        }
    }

    public function testAuthAccountSecure ($hostname, $hostport, $username, $password, $account)
    {
        $this->ftpClient->secure = TRUE;
        print("Connecting to FTP server..\r\n");
        if($this->ftpClient->connect($hostname, $hostport))
        {
            print("Succussfully connected to " . $hostname . "\r\n");
    
        }
        else
        {
            print("Failed to connect to " . $hostname . "\r\n");
            die(1);
        }
    
        if($this->ftpClient->authenticateAccount($username, $password, $account))
        {
            print("Authentication successful.\r\n");
           
        }
        else
        {
            print("Authentication failed.\r\n");
            die(1);
        }
    }

    public function testUsePassive($passive)
    {
        if($this->ftpClient->usePassive($passive))
        {
           print("Changed to passive mode.\r\n");
        }
         else
        {
            print("Unable to turn on passive mode.\r\n");
            die(1);
        }
    }

    public function testPwd()
    {
        print("Getting current working directory (pwd).\r\n");
        return $this->ftpClient->pwd();
    }

    public function testListRaw ($directoryWithPattern, $recursive = FALSE)
    {
        print("Getting a directory listing using pattern: " . $directoryWithPattern . "\r\n");
        return $this->ftpClient->listRaw($directoryWithPattern, $recursive);
        
    }

    public function testUploadFile ($destinationFilename, $sourceFilename)
    {
        print("Uploading: " . $sourceFilename . " To: " . $destinationFilename . "\r\n");
        return $this->ftpClient->uploadFile($destinationFilename, $sourceFilename);
    }

    public function testDownloadFile ($destinationFilename, $sourceFilename)
    {
        print("Downloading: " . $sourceFilename . " To: " . $destinationFilename . "\r\n");
        return $this->ftpClient->downloadFile($destinationFilename, $sourceFilename);
    }

    public function testDeleteFile ($filename)
    {
        print("Deleting file: " . $filename . "\r\n");
        return $this->ftpClient->deleteFile($filename);
    }
}
?>