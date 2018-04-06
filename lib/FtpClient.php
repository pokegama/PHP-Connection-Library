<?php
/**
 * FtpClient.php
 *
 * lib/FtpClient.php
 *
 * PHP-FTP-Client
 */

/**
 * class FtpClient
 *
 * Uses PHP builtin FTP https://secure.php.net/manual/en/book.ftp.php
 */
class FtpClient {
    // The connection handle.
    protected $conn = null;

    // Determine if SSL / TLS encryption layer should be used.
    public $secure = TRUE;

    // The timeout for all network operations.
    public $timeout = 90;

    // Determine if passive mode should be on or off
    public $passive = FALSE;

    // Binary transfer will copy the file bit for bit.  ASCII mode can make
    // changes to the file in certain circumstances.  Line endings might be
    // changed when using FTP_ASCII
    public $ftpTransferMode = FTP_BINARY;


    /**
     * Instantiate an FtpClient
     *
     * @param void
     *
     * @return void
     */
    public function __construct()
    {
        // This class has no dependencies outside of PHP FTP
    }

    /**
     * Clean up after the FtpClient.
     *
     * @param void
     *
     * @return void
     */
    public function __destruct()
    {
        // Close the FTP stream
        if($this->conn)
        {
            ftp_close($this->conn);
            $this->conn = null;
        }
    }

    /**
     * Connect to the FTP server.
     *
     * Determine if this connection is over secure socket layer, then attempt
     * to connect using the appropriate function.  The default behavior is to
     * setup a secure connection.
     *
     * @param string $hostname The name or address of the FTP server.
     *
     * @param int $port The port number for the FTP server.
     *
     * @param bool $ssl Will the connection be secured with SSL/TLS.
     *
     * @return bool 
     */
    public function connect (string $hostname, int $port = 21)
    {
        // Check if this connection is secure
        if($this->secure)
        {
            $this->conn = @ftp_ssl_connect($hostname, $port, $this->timeout);
        }
        else
        {
            $this->conn = @ftp_connect($hostname, $port, $this->timeout);
        }

        // Check if the FTP stream was established
        if($this->conn)
        {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Authenticate with the FTP server normally.
     *
     * Do a typical username and password login.
     *
     * @param string $username The username for the account
     *
     * @param string $password The password for the account
     *
     * @return bool 
     */
    public function authenticateNormal (string $username, string $password)
    {
        // Attempt to authenticate
        if(@ftp_login($this->conn, $username, $password))
        {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Authenticate with the FTP server and also send the account name.
     *
     * Do a typical username and password login, then send a special ACCT
     * command to provide the server with the account credential.
     *
     * @param string $username The username for the account
     *
     * @param string $password The password for the account
     *
     * @param string $account The account name for the account
     *
     * @return bool 
     */
    public function authenticateAccount (string $username, string $password, string $account)
    {
        // The ftp_login function must be used instead of passing in the
        // username and password via raw commands to ensure that SSL won't be
        // dropped during the connection.
        // For example, if you just do the following, SSL won't be setup even
        // if ftp_ssl_connect() was used to setup the connection handle.
        // $serverResponse = array();
        // $serverResponse[] = ftp_raw($conn, "USER " . $username);
        // $serverResponse[] = ftp_raw($conn, "PASS " . $password);
        // $serverResponse[] = ftp_raw($conn, "ACCT " . $account);

        // Suppress the warnings that will almost definitely get kicked out by
        // PHP.  It's okay, even though it says it's not authenticated, it will
        // be after we pass in the ACCT command.
        @ftp_login($this->conn, $username, $password);
        // Now pass in the account string.
        $serverResponse = ftp_raw($this->conn, "ACCT " . $account);
        // See if the server responded with a "230" code.  It's a good thing if
        // it did so.
        if(preg_match("/230/", $serverResponse[0]) === 1)
        {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Turn FTP passive mode on or off.
     *
     * In passive mode, data connections are initiated by the client, rather
     * than by the server. It may be needed if the client is behind firewall.
     *
     * @param bool $passive Should passive mode be turned on?
     *
     * @return bool 
     */
    public function usePassive (bool $passive)
    {
        if(ftp_pasv($this->conn, $passive))
        {
            $this->passive = $passive;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Returns the current directory name.
     *
     * Query the server for the current working directory and return the result.
     *
     * @param void
     *
     * @return string The name of the current working directory.
     */
    public function pwd ()
    {
        return ftp_pwd($this->conn);
    }

    /**
     * Returns a raw directory listing
     *
     * Returns an array where each element corresponds to one line of text.
     * Returns FALSE when passed directory is invalid.
     * The output is not parsed in any way. The system type identifier returned
     * by ftp_systype() can be used to determine how the results should be
     * interpreted.
     *
     * @param string $directoryWithPattern The directory with any wildcard
     *      patterns you are looking for.  Example: "/*.xml"
     *
     * @param bool $recursive Should the listing be recursive?
     *
     * @return array or bool. The raw listing result or FALSE.
     */
    public function listRaw($directoryWithPattern, $recursive = FALSE)
    {
        return ftp_rawlist($this->conn, $directoryWithPattern, $recursive);
    }

    /**
     * Uploads a file to the FTP server
     *
     * Copy a file to the FTP server.  Returns TRUE on success and FALSE on
     * failure.  This may also overwrite any existing files with the same
     * name on the remote system.
     *
     * @param string $destinationFilename The full remote filename for the
     *      destination file.
     *
     * @param string $sourceFilename The full local filename for the source
     *      file.
     *
     * @return bool
     */
    public function uploadFile ($destinationFilename, $sourceFilename)
    {
        return ftp_put($this->conn, $destinationFilename, $sourceFilename, $mode = $this->ftpTransferMode);
    }

    /**
     * Downloads a file from the FTP server
     *
     * Retrieves a remote file from the FTP server, and saves it into a local
     * file.  This may also overwrite any existing files with the same name on
     * the local system.
     *
     * @param string $destinationFilename The full remote filename for the
     *      destination file.
     *
     * @param string $sourceFilename The full local filename for the source
     *      file.
     *
     * @return bool
     */
    public function downloadFile ($destinationFilename, $sourceFilename)
    {
        return ftp_get($this->conn, $destinationFilename, $sourceFilename, $mode = $this->ftpTransferMode);
    }

    /**
     * Deletes a file on the FTP server
     *
     * @param string $filename The full remote filename to delete.
     *
     * @return bool
     */
    public function deleteFile ($filename)
    {
        return ftp_delete($this->conn, $filename);
    }
}

?>