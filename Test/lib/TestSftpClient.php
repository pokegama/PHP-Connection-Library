<?php

class TestSftpClient {
    private $sftpClient = null;
    private $testFailed = FALSE;
    private $errorMessages = array();

    // Server attributes
    private $hostname = "";
    private $port = "";
    private $key_md5_fingerprint = "";
    private $passwordAuthentication = False;
    private $publicKeyAuthentication = False;
    private $remoteInboundDir = "";
    private $remoteOutboundDir = "";
    private $remoteLooptestDir = "";

    // Client attributes
    private $username = "";
    private $password = "";
    private $dsaNoPassphrasePub = "";
    private $dsaNoPassphrasePriv = "";
    private $dsaWithPassphrasePub = "";
    private $dsaWithPassphrasePriv = "";
    private $rsaNoPassphrasePub = "";
    private $rsaNoPassphrasePriv = "";
    private $rsaWithPassphrasePub = "";
    private $rsaWithPassphrasePriv = "";
    private $privKeyPassphrase = "";
    private $methods = "";
    private $localInboundDir = "";
    private $localOutboundDir = "";
    private $localLooptestDir = "";


    public function __construct($testParams)
    {
        // Server attributes
        $this->hostname                = $testParams["sftp_server"]["hostname"];
        $this->port                    = $testParams["sftp_server"]["port"];
        $this->key_md5_fingerprint     = $testParams["sftp_server"]["key_md5_fingerprint"];
        $this->remoteInboundDir        = $testParams["sftp_server"]["inbound_directory"];
        $this->remoteOutboundDir       = $testParams["sftp_server"]["outbound_directory"];
        $this->remoteLooptestDir       = $testParams["sftp_server"]["looptest_directory"];
        // Client attributes
        $this->username                = $testParams["sftp_client"]["username"];
        $this->password                = $testParams["sftp_client"]["password"];
        $this->dsaNoPassphrasePub      = $testParams["sftp_client"]["dsa_no_passphrase_pub"];
        $this->dsaNoPassphrasePriv     = $testParams["sftp_client"]["dsa_no_passphrase_priv"];
        $this->dsaWithPassphrasePub    = $testParams["sftp_client"]["dsa_with_passphrase_pub"];
        $this->dsaWithPassphrasePriv   = $testParams["sftp_client"]["dsa_with_passphrase_priv"];
        $this->rsaNoPassphrasePub      = $testParams["sftp_client"]["rsa_no_passphrase_pub"];
        $this->rsaNoPassphrasePriv     = $testParams["sftp_client"]["rsa_no_passphrase_priv"];
        $this->rsaWithPassphrasePub    = $testParams["sftp_client"]["rsa_with_passphrase_pub"];
        $this->rsaWithPassphrasePriv   = $testParams["sftp_client"]["rsa_with_passphrase_priv"];
        $this->privKeyPassphrase       = $testParams["sftp_client"]["priv_key_passphrase"];
        $this->methods                 = $testParams["sftp_client"]["connection_methods"];
        $this->localInboundDir         = $testParams["sftp_client"]["inbound_directory"];
        $this->localOutboundDir        = $testParams["sftp_client"]["outbound_directory"];
        $this->localLooptestDir        = $testParams["sftp_client"]["looptest_directory"];
    }

    public function __destruct()
    {
        $this->sftpClient = null;
    }

    public function testQueryHostKeyFingerprint()
    {
        if(!$this->sftpClient)
        {
            $this->sftpClient = new SftpClient();
            $this->sftpClient->connect($this->hostname, $this->port);
        }
        echo "Executing method: queryHostKeyFingerprint...\r\n";
        $fingerprint = $this->sftpClient->queryHostKeyFingerprint();
        if(!empty($fingerprint))
        {
            echo "Host replied with MD5 Fingerprint: " . $fingerprint . "\r\n";
            return TRUE;
        }
        else
        {
            echo "Server did not respond to the host key fingerprint query.\r\n";
            return FALSE;
        }
    }

    public function testVerifyHostKeyFingerprint()
    {
        if(!$this->sftpClient)
        {
            $this->sftpClient = new SftpClient();
            $this->sftpClient->connect($this->hostname, $this->port, $this->methods);
        }
        echo "Executing method: verifyHostKeyFingerprint...\r\n";
        if($this->sftpClient->verifyHostKeyFingerprint($this->key_md5_fingerprint))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    public function testPasswordAuthentication()
    {
        if(!$this->sftpClient)
        {
            $this->sftpClient = new SftpClient();
            $this->sftpClient->connect($this->hostname, $this->port, $this->methods);
        }
        // Password authentication
        echo "Executing method: passwordAuthentication...\r\n";
        if($this->sftpClient->passwordAuthentication($this->username, $this->password))
        {
          echo "Password authentication successful.\r\n";
          return TRUE;
        }
        else
        {
            echo "Password authentication failed.\r\n";
            return TRUE;
        }
    }

    public function testPublicKeyAuthenticationNoPrivKeyPassphrase()
    {
        
        //
        // DSA without passphrase
        //
        echo "Testing public key authentication for DSA with no passphrase.\r\n";
        if(!$this->sftpClient)
        {
            $this->sftpClient = new SftpClient();
            $this->sftpClient->connect($this->hostname, $this->port, $this->methods);
        }
        echo "Executing method: publicKeyAuthentication...\r\n";
        if($this->sftpClient->publicKeyAuthentication($this->username, $this->dsaNoPassphrasePub, $this->dsaNoPassphrasePriv))
        {
          echo "DSA with no passphrase authentication successful.\r\n";
          $this->sftpClient->__destruct();
          $this->sftpClient = null;
          sleep(2);
        }
        else
        {
            echo "DSA with no passphrase authentication failed.\r\n";
            die(1);
        }
        

        //
        // RSA without passphrase
        //
        echo "Testing public key authentication for RSA with no passphrase.\r\n";
        if(!$this->sftpClient)
        {
            $this->sftpClient = new SftpClient();
            $this->sftpClient->connect($this->hostname, $this->port, $this->methods);
        }
        echo "Executing method: publicKeyAuthentication...\r\n";
        if($this->sftpClient->publicKeyAuthentication($this->username, $this->rsaNoPassphrasePub, $this->rsaNoPassphrasePriv))
        {
          echo "RSA with no passphrase authentication successful.\r\n";
          $this->sftpClient->__destruct();
          $this->sftpClient = null;
        }
        else
        {
            echo "RSA with no passphrase authentication failed.\r\n";
            die(1);
        }
    }

    /**
     * This will fail on Debian 9 because of a know bug with PHP SSH2
     */
    public function testPublicKeyAuthenticationWithPrivKeyPassphrase()
    {
        
        //
        // DSA with passphrase
        //
        echo "Testing public key authentication for DSA with passphrase.\r\n";
        if(!$this->sftpClient)
        {
            $this->sftpClient = new SftpClient();
            $this->sftpClient->connect($this->hostname, $this->port, $this->methods);
        }
        echo "Executing method: publicKeyAuthentication...\r\n";
        if($this->sftpClient->publicKeyAuthentication($this->username, $this->dsaWithPassphrasePub, $this->dsaWithPassphrasePriv, $this->privKeyPassphrase))
        {
          echo "DSA with passphrase authentication successful.\r\n";
          $this->sftpClient = null;
          sleep(2);
        }
        else
        {
            echo "DSA with passphrase authentication failed.\r\n";
            die(1);
        }
        

        //
        // RSA with passphrase
        //
        echo "Testing public key authentication for RSA with passphrase.\r\n";
        if(!$this->sftpClient)
        {
            $this->sftpClient = new SftpClient();
            $this->sftpClient->connect($this->hostname, $this->port, $this->methods);
        }
        echo "Executing method: publicKeyAuthentication...\r\n";
        if($this->sftpClient->publicKeyAuthentication($this->username, $this->rsaWithPassphrasePub, $this->rsaWithPassphrasePriv, $this->privKeyPassphrase))
        {
          echo "RSA with passphrase authentication successful.\r\n";
          $this->sftpClient = null;
          sleep(2);
        }
        else
        {
            echo "RSA with passphrase authentication failed.\r\n";
            die(1);
        }
    }

    public function testPasswordAndPublicAuthentication()
    {
        echo "Testing password and public key authentication for RSA with no passphrase.\r\n";
        if(!$this->sftpClient)
        {
            $this->sftpClient = new SftpClient();
            $this->sftpClient->connect($this->hostname, $this->port, $this->methods);
        }
        echo "Executing method: passwordAndPublicKeyAuthentication...\r\n";
        if($this->sftpClient->passwordAndPublicKeyAuthentication($this->username, $this->password, $this->rsaNoPassphrasePub, $this->rsaNoPassphrasePriv))
        {
          echo "Passowrd and public key authentication successful.\r\n";
          $this->sftpClient->__destruct();
          $this->sftpClient = null;
        }
        else
        {
            echo "Passowrd and public key authentication failed.\r\n";
            die(1);
        }
    }

    public function testList()
    {
        if(!$this->sftpClient)
        {
            $this->sftpClient = new SftpClient();
            $this->sftpClient->connect($this->hostname, $this->port, $this->methods);
        }
        if($this->testVerifyHostKeyFingerprint())
        {
            print("Host key fingerprint verified.\r\n");
            if($this->sftpClient->passwordAuthentication($this->username, $this->password))
            {
                echo "Executing method: list...\r\n";
                $directoryListing = $this->sftpClient->list($this->remoteLooptestDir);
                var_dump($directoryListing);
            }
        }
    }

    public function testListDetails()
    {
        if(!$this->sftpClient)
        {
            $this->sftpClient = new SftpClient();
            $this->sftpClient->connect($this->hostname, $this->port, $this->methods);
        }
        if($this->testVerifyHostKeyFingerprint())
        {
            print("Host key fingerprint verified.\r\n");
            if($this->sftpClient->passwordAuthentication($this->username, $this->password))
            {
                echo "Executing method: listDetails...\r\n";
                $directoryListing = $this->sftpClient->listDetails($this->remoteLooptestDir);
                var_dump($directoryListing);
            }
        }
    }

    public function testUpload()
    {
        if(!$this->sftpClient)
        {
            $this->sftpClient = new SftpClient();
            $this->sftpClient->connect($this->hostname, $this->port, $this->methods);
        }
        if($this->testVerifyHostKeyFingerprint())
        {
            print("Host key fingerprint verified.\r\n");
            if($this->sftpClient->passwordAuthentication($this->username, $this->password))
            {
                foreach(scandir($this->localLooptestDir) as $dirObject)
                {
                    if (strcmp($dirObject, ".") != 0 and strcmp($dirObject, "..") != 0)
                    {
                        if(!is_dir($dirObject))
                        {
                            $sourceFilename = $this->localLooptestDir . "/" . $dirObject;
                            $destinationFilename = $this->remoteLooptestDir . "/" . $dirObject;
                            echo "Executing method: uploadFile...\r\n";
                            echo "Uploading: " . $dirObject . "\r\n";
                            if(!$this->sftpClient->uploadFile($sourceFilename, $destinationFilename))
                            {
                                throw new Exception("File upload verification failed!");
                            }
                        }
                    }
                }
            }
        }
    }

    public function testAuthUpDownListDel()
    {
        echo "Executing upload/download loop test...\r\n";
        if(!$this->sftpClient)
        {
            $this->sftpClient = new SftpClient();
            $this->sftpClient->connect($this->hostname, $this->port, $this->methods);
        }
        if($this->testVerifyHostKeyFingerprint())
        {
            print("Host key fingerprint verified.\r\n");
            if($this->sftpClient->passwordAuthentication($this->username, $this->password))
            {
                foreach(scandir($this->localLooptestDir) as $dirObject)
                {
                    if (strcmp($dirObject, ".") != 0 and strcmp($dirObject, "..") != 0)
                    {
                        if(!is_dir($dirObject))
                        {
                            $sourceFilename = $this->localLooptestDir . "/" . $dirObject;
                            $destinationFilename = $this->remoteLooptestDir . "/" . $dirObject;
                            echo "Executing method: uploadFile...\r\n";
                            echo "Uploading: " . $dirObject . "\r\n";
                            if(!$this->sftpClient->uploadFile($sourceFilename, $destinationFilename))
                            {
                                throw new Exception("File upload verification failed!");
                            }
                        }
                    }
                }
                echo "Executing method: list...\r\n";
                $directoryListing = $this->sftpClient->list($this->remoteLooptestDir);
                foreach($directoryListing['Files'] as $remoteFile)
                {
                    $sourceFilename = $this->remoteLooptestDir . "/" . $remoteFile["name"];
                    $destinationFilename = $this->localInboundDir . "/" . $remoteFile["name"];
                    echo "Executing method: downloadFile...\r\n";
                    echo "Downloading: " . $remoteFile["name"] . "\r\n";
                    if(!$this->sftpClient->downloadFile($sourceFilename, $destinationFilename))
                    {
                        throw new Exception("File download verification failed!");
                    }
                    $this->sftpClient->deleteFile($sourceFilename);
                }
            }
        }
    }

    public function testChmod()
    {
        $filename = $this->remoteLooptestDir . "/" . "A_Long_Journey_Ahead.jpg";
        $mode = 0777;
        if(!$this->sftpClient)
        {
            $this->sftpClient = new SftpClient();
            $this->sftpClient->connect($this->hostname, $this->port, $this->methods);
        }
        if($this->sftpClient->passwordAuthentication($this->username, $this->password))
        {
            echo "Executing method: chmod...\r\n";
            if($this->sftpClient->chmod($filename, $mode))
            {
                echo "Executing method: listDetails...\r\n";
                $directoryListing = $this->sftpClient->listDetails($this->remoteLooptestDir);
                var_dump($directoryListing);
            }
        }
    }

    public function localTestDirFileListing()
    {
        echo "Searching for files in local Test/IO-Dir/...\r\n";
        $localDirectories = array($this->localInboundDir,
                                  $this->localOutboundDir,
                                  $this->localLooptestDir);
        $fileListing = array();
        foreach($localDirectories as $localDirectory)
        {
            foreach(scandir($localDirectory) as $dirObject)
            {
                if (strcmp($dirObject, ".") != 0 and strcmp($dirObject, "..") != 0)
                {
                    if(!is_dir($dirObject))
                    {
                        $fileRelativePathname = $localDirectory . "/" . $dirObject;
                        echo $fileRelativePathname . "\r\n";
                        $fileListing[$localDirectory][] = $dirObject;
                    }
                }
            }
        }
    }
}