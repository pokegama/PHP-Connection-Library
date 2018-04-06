<?php
/**
 * SftpClient.php
 *
 * lib/SftpClient.php
 *
 * PHP-SFTP-Client
 */

/**
 * class SftpClient
 *
 * Uses PHP builtin SSH2 https://secure.php.net/manual/en/book.ssh2.php
 * Which uses libssh2 https://libssh2.org/
 */
// --------------------------------------------------------------------
class SftpClient {

    // Server connection handle
    protected $conn = null;
    // SFTP subsystem handle 
    protected $sftp = null;
    // Has the client authenticated with server
    public $auth    = FALSE;
    // Prefer stronger algorithms over weaker ones.
    public $methods = array('kex' => 'diffie-hellman-group-exchange-sha256',
                                     'diffie-hellman-group-exchange-sha1',
                                     'diffie-hellman-group14-sha1',
                                     'diffie-hellman-group1-sha1',
                            'client_to_server' => array('crypt' => 'aes256-ctr',
                                                                   'aes192-ctr',
                                                                   'aes128-ctr',
                                                                   'aes256-cbc',
                                                                   'aes192-cbc',
                                                                   'aes128-cbc',
                                                                   '3des-cbc',
                                                                   'blowfish-cbc',
                                                                   'cast128-cbc',
                                                                   'arcfour',
                                                                   'arcfour128',
                                                        'mac' => 'hmac-sha2-256',
                                                                 'hmac-sha2-512',
                                                                 'hmac-sha1',
                                                                 'hmac-sha1-96',
                                                                 'hmac-md5',
                                                                 'hmac-md5-96',
                                                                 'hmac-ripemd160'),
                            'server_to_client' => array('crypt' => 'aes256-ctr',
                                                                   'aes192-ctr',
                                                                   'aes128-ctr',
                                                                   'aes256-cbc',
                                                                   'aes192-cbc',
                                                                   'aes128-cbc',
                                                                   '3des-cbc',
                                                                   'blowfish-cbc',
                                                                   'cast128-cbc',
                                                                   'arcfour',
                                                                   'arcfour128',
                                                        'mac' => 'hmac-sha2-256',
                                                                 'hmac-sha2-512',
                                                                 'hmac-sha1',
                                                                 'hmac-sha1-96',
                                                                 'hmac-md5',
                                                                 'hmac-md5-96',
                                                                 'hmac-ripemd160'));
    //private $callbacks = array('disconnect' => [$this, 'serverDisconnectReceived']);

    /**
     * Instantiate the SftpClient
     *
     * @param void
     *
     * @return void
     */
    public function __construct()
    {
        // This class has no dependencies outside of PHP SSH2
    }

    /**
     * Clean up after the SftpClient.
     *
     * @param void
     *
     * @return void
     */
    public function __destruct()
    {
        $this->conn = null;
        $this->sftp = null;
    }

    /**
     * Handle a server initiated disconnect.
     *
     * Attempt to handle a server disconnect in a more graceful manner than
     * might otherwise happen by default.  Try to Notify the user why the
     * server terminates the connection.
     *
     * @param int $reason Reason code for disconnect.
     *
     * @param string $message Reason message for disconnect.
     *
     * @param string $language Reason language for disconnect.
     *
     * @return void
     */
    public function serverDisconnectReceived($reason, $message, $language)
    {
        printf("Server disconnected with reason code [%d] and message: %s\n", $reason, $message);
    }

    /**
     * Connect to the SFTP server.
     *
     * This is basically a wrapper for the ssh2_connect function as described
     *      in: https://secure.php.net/manual/en/function.ssh2-connect.php
     *
     * @param string $hostname The name or address of the SFTP server.
     *
     * @param int $port The port number for the SFTP server.
     *
     * @param array $methods An associative array with connection parameters.
     *
     * @return bool 
     */
    public function connect(string $hostname, int $port = 22, array $methods = array())
    {
        // Use methods provided first.
        $methods = array_filter($methods);
        if(!empty($methods))
        {
            $this->methods = $methods;
        }
        //$this->conn = ssh2_connect($hostname, $port, $this->methods, $this->callbacks);
        if($this->conn = ssh2_connect($hostname, $port, $this->methods))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }

    }

    /**
     * Request the server to reply with it's host key fingerprint.
     *
     * @param string $algorithm The PHP ssh2_fingerprint only supports the MD5
     *      and SHA1 algorithms.  Acceptable values are 'md5' and 'sha1'.
     *
     * @return string $fingerprint The host key fingerprint as a hash string.
     *      If the server did not respond, an empty string is returned.
     */
    public function queryHostKeyFingerprint(string $algorithm="md5")
    {
        switch($algorithm)
        {
          case "md5":
              $flags = SSH2_FINGERPRINT_MD5;
              break;
          case "sha1":
              $flags = SSH2_FINGERPRINT_SHA1;
              break;
          default:
              $flags = SSH2_FINGERPRINT_MD5;
              break;
        }
        $fingerprint = ssh2_fingerprint($this->conn, $flags);
        if(!empty($fingerprint))
        {
          return $fingerprint;
        }
        else
        {
            return "";
        }
    }

    /**
     * Compare the server host key with an expected value.
     *
     * This is a fundamental task in the SSH connection process that is often
     * neglected.  The host key fingerprint is a way of uniquely identifying a
     * server.  This is used to thwart man-in-the-middle attacks.
     *
     * @param string $expectedFingerprint The expected value the server should
     *      reply with.  An MD5 example "F24507F6DED52EE979303AD12A1E20A9".
     *
     * @param string $algorithm The PHP ssh2_fingerprint only supports the MD5
     *      and SHA1 algorithms.  Acceptable values are 'md5' and 'sha1'.
     *
     * @return bool
     */
    public function verifyHostKeyFingerprint(string $expectedFingerprint, string $algorithm="md5")
    {
        switch($algorithm)
        {
          case "md5":
              $flags = SSH2_FINGERPRINT_MD5;
              break;
          case "sha1":
              $flags = SSH2_FINGERPRINT_SHA1;
              break;
          default:
              $flags = SSH2_FINGERPRINT_MD5;
              break;
        }
        $fingerprint = ssh2_fingerprint($this->conn, $flags);
        if(strcmp($fingerprint,$expectedFingerprint) == 0)
        {
          return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Authenticate with the server using a password.
     *
     * @param string $username The username for the SFTP user account.
     *
     * @param string $password The password for the SFTP user account.
     *
     * @return bool
     */
    public function passwordAuthentication(string $username, string $password)
    {
        if($this->auth)
        {
            throw new Exception("Already authenticated.");
        }
        elseif (ssh2_auth_password($this->conn, $username, $password))
        {
            $this->auth = TRUE;
            $this->requestSftp();
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Authenticate with the server using public key authentication.
     *
     * Please note that there is an unresolved problem with decrypting the
     * private key under certain linux distributions.
     * https://bugs.php.net/bug.php?id=58573
     *
     * @param string $username The username for the SFTP user account.
     *
     * @param string $pubkey The filepath to the user's public key file.
     *
     * @param string $privkey The filepath to the user's private key file.
     *
     * @param string $passphrase The passphrase to decrypt the user's private key.
     *
     * @return bool
     */
    public function publicKeyAuthentication(string $username, string $pubkey, string $privkey, string $passphrase="")
    {
        if($this->auth)
        {
            throw new Exception("Already authenticated.");
        }
        elseif(strcmp($passphrase, "") == 0)
        {
            
            if (ssh2_auth_pubkey_file($this->conn, $username, $pubkey, $privkey))
            {
                $this->auth = TRUE;
                $this->requestSftp();
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            if (ssh2_auth_pubkey_file($this->conn, $username, $pubkey, $privkey, $passphrase))
            {
                $this->auth = TRUE;
                $this->requestSftp();
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
    }

    /**
     * Authenticate with the server using a password and public key.
     *
     * @param string $username The username for the SFTP user account.
     *
     * @param string $password The password for the SFTP user account.
     *
     * @param string $pubkey The filepath to the user's public key file.
     *
     * @param string $privkey The filepath to the user's private key file.
     *
     * @return bool
     */
    public function passwordAndPublicKeyAuthentication(string $username, string $password, string $pubkey, string $privkey)
    {
        if($this->auth)
        {
            throw new Exception("Already authenticated.");
        }
        else
        {
            /*
                From: https://secure.php.net/manual/en/function.ssh2-auth-pubkey-file.php
                """
                The underlying libssh library doesn't support partial auths
                very cleanly That is, if you need to supply both a public key
                and a password it will appear as if this function has failed.
                In this particular case a failure from this call may just mean
                that auth hasn't been completed yet. You would need to ignore
                this failure and continue on and call ssh2_auth_password() in
                order to complete authentication.
                """

                So that's why we suppress errors produced by ssh2_auth_pubkey_file
            */
            @ssh2_auth_pubkey_file($this->conn, $username, $pubkey, $privkey);
            if(ssh2_auth_password($this->conn, $username, $password))
            {
                $this->auth = TRUE;
                $this->requestSftp();
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
    }

    /**
     * Initialize the SFTP subsystem
     *
     * @param void
     *
     * @return bool
     */
    public function requestSftp()
    {
        if($this->auth)
        {
            //$this->sftp = ssh2_sftp($this->conn);
            if($this->sftp = ssh2_sftp($this->conn))
            {
                return TRUE;
            }
            else
            {
                throw new Exception("Request for SFTP subsystem failed or was denied by the host system.");
            }
        }
    }

    /**
     * Return an array of filesystem object names in a remote SFTP directory.
     *
     * @param arracy $remoteDirectory The directory to list.
     *
     * @return array $dirObjList If anything is found, returns an associative
     *      array with key "Directories" containing an indexed array of
     *      child directories and with key "Files" containing an indexed array
     *      of files.
     */
    public function list(string $remoteDirectory)
    {
        if($this->sftp)
        {
            $directoryResource = "ssh2.sftp://$this->sftp$remoteDirectory";
            $dirObjList = array();
            $directoryHandle = opendir($directoryResource);
            // List all the directory objects
            while (false !== ($dirObject = readdir($directoryHandle)))
            {
                if (strcmp($dirObject, ".") != 0 and strcmp($dirObject, "..") != 0)
                {
                    if(is_file("ssh2.sftp://$this->sftp$remoteDirectory/$dirObject"))
                    {
                        $dirObjList['Files'][] = array("name" => $dirObject);
                    }
                    elseif(is_dir("ssh2.sftp://$this->sftp$remoteDirectory/$dirObject"))
                    {
                        $dirObjList['Directories'][] = $dirObject;
                    }
                }
            }
            closedir($directoryHandle);
            return $dirObjList;
        }
        else
        {
            throw new Exception("SFTP subsystem has not been initialized.");
        }
    }

    /**
     * Return an array of filesystem object names in a remote SFTP directory.
     *
     * @param arracy $remoteDirectory The directory to list.
     *
     * @return array $dirObjList If anything is found, returns an associative
     *      array with key "Directories" containing an indexed array of
     *      child directories and with key "Files" containing an indexed array
     *      of files.
     */
    public function listDetails(string $remoteDirectory)
    {
        if($this->sftp)
        {
            $directoryResource = "ssh2.sftp://$this->sftp$remoteDirectory";
            $dirObjList = array();
            $directoryHandle = opendir($directoryResource);
            // List all the directory objects
            while (false !== ($dirObject = readdir($directoryHandle)))
            {
                if (strcmp($dirObject, ".") != 0 and strcmp($dirObject, "..") != 0)
                {
                    if(is_file("ssh2.sftp://$this->sftp$remoteDirectory/$dirObject"))
                    {
                        $fullFilename = $remoteDirectory . "/" . $dirObject;
                        $fileDetails = ssh2_sftp_stat($this->sftp, $fullFilename);
                        $dirObjList['Files'][] = array("name" => $dirObject,
                                                       "mode" => $fileDetails["mode"],
                                                       "uid" => $fileDetails["uid"],
                                                       "gid" => $fileDetails["gid"],
                                                       "size" => $fileDetails["size"],
                                                       "mtime" => $fileDetails["mtime"],
                                                       "atime" => $fileDetails["atime"]);
                    }
                    elseif(is_dir("ssh2.sftp://$this->sftp$remoteDirectory/$dirObject"))
                    {
                        $dirObjList['Directories'][] = $dirObject;
                    }

                    
                }
            }
            closedir($directoryHandle);
            return $dirObjList;
        }
        else
        {
            throw new Exception("SFTP subsystem has not been initialized.");
        }
    }

    /**
     * Download a file from the SFTP server.
     *
     * @param string $sourceFilename The remote filename you wish to download.
     *
     * @param string $destinationFilename The local filename you wish to write.
     *      the file contents to.  This will overwrite any existing files with
     *      the same name.
     *
     * @return bool
     */
    public function downloadFile(string $sourceFilename, string $destinationFilename)
    {
        if($this->sftp)
        {
            $sourceFileContents = null;
            $fileSize = filesize("ssh2.sftp://$this->sftp$sourceFilename");
            if(!$stream = fopen("ssh2.sftp://$this->sftp$sourceFilename", 'r'))
            {
                throw new Exception("File read access denied: $sourceFilename");
            }
            $read = 0;
            while($read < $fileSize and ($readBuffer = fread($stream, $fileSize - $read)))
            {
                $read += strlen($readBuffer);
                $sourceFileContents .= $readBuffer;
            }
            file_put_contents ($destinationFilename, $sourceFileContents);
            fclose($stream);
            if(is_file($destinationFilename))
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            throw new Exception("SFTP subsystem has not been initialized.");
        }
    }

    /**
     * Upload a file to the SFTP server.
     *
     * @param string $sourceFilename The local filename you wish to upload.
     *
     * @param string $destinationFilename The remote filename you wish to write
     *      to.  This will overwrite any existing file with the same name.
     *
     * @param bool
     */
    public function uploadFile(string $sourceFilename, string $destinationFilename)
    {
        if($this->sftp)
        {
            if (!$stream = fopen("ssh2.sftp://$this->sftp$destinationFilename", 'w'))
            {
                throw new Exception("File write access denied: $destinationFilename");
            }
            if (!$uploadFileData = file_get_contents($sourceFilename))
            {
                throw new Exception("Failed to open: $sourceFilename.");
            }
            if (!fwrite($stream, $uploadFileData))
            {
                throw new Exception("Failed to upload: $sourceFilename.");
            }
            fclose($stream);
            // There are some SFTP servers that do strange things to uploaded
            // files, but generally speaking this will verify that the file
            // was actually uploaded successfully.
            if(is_file("ssh2.sftp://$this->sftp$destinationFilename"))
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            throw new Exception("SFTP subsystem has not been initialized.");
        }
    }

    /**
     * Delete a file on the SFTP server
     *
     * @param string $filename The name of the file.
     *
     * @return void
     */
    public function deleteFile(string $filename)
    {
        if($this->sftp)
        {
            if(is_dir("ssh2.sftp://$this->sftp$filename"))
            {
                throw new Exception("Wrong method called for deleting a directory.");
            }
            else
            {
                unlink("ssh2.sftp://$this->sftp$filename");
            }
        }
        else
        {
            throw new Exception("SFTP subsystem has not been initialized.");
        }
    }

    /**
     * Check if a file exists on the SFTP server
     *
     * @param string $filename The name of the file.
     *
     * @return bool
     */
    public function checkForFilename(string $filename)
    {
        if($this->sftp)
        {
            if(file_exists("ssh2.sftp://$this->sftp$filename"))
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            throw new Exception("SFTP subsystem has not been initialized.");
        }

    }

    /**
     * Modify permissions of a file on the SFTP server.
     *
     * @param string $filename The name of the file.
     *
     * @param int $mode The permissions in octal form. Ex. 0755
     *
     * @return bool
     */
    public function chmod(string $filename, int $mode)
    {
        if($this->sftp)
        {
            if(file_exists("ssh2.sftp://$this->sftp$filename"))
            {
                if(ssh2_sftp_chmod($this->sftp, $filename, $mode))
                {
                    return TRUE;
                }
                return FALSE;
            }
            else
            {
                throw new Exception("The filename does not exist.");
            }
        }
        else
        {
            throw new Exception("SFTP subsystem has not been initialized.");
        }

    }
}

?>



