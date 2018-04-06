<?php

require 'Test/lib/TestSftpClient.php';
require 'lib/SftpClient.php';


// Server Settings - This server is not available on the Internet yet.
$testParameters["sftp_server"]["hostname"]                 = 'sftp-server1.devsblock.com';
$testParameters["sftp_server"]["port"]                     = 22;
$testParameters["sftp_server"]["key_md5_fingerprint"]      = 'F24507F6DED52EE979303AD12A1E20A9';
$testParameters["sftp_server"]["inbound_directory"]        = '/inbound';
$testParameters["sftp_server"]["outbound_directory"]       = '/outbound';
$testParameters["sftp_server"]["looptest_directory"]       = '/loop';
// Client Settings
$testParameters["sftp_client"]["username"]                 = 'testuser';
$testParameters["sftp_client"]["password"]                 = 'c94-HGPLM!N13XDSLKH$';
$testParameters["sftp_client"]["dsa_no_passphrase_pub"]    = 'Test/SSH-Keys/dsa_no_passphrase.pub';
$testParameters["sftp_client"]["dsa_no_passphrase_priv"]   = 'Test/SSH-Keys/dsa_no_passphrase';
$testParameters["sftp_client"]["dsa_with_passphrase_pub"]  = 'Test/SSH-Keys/dsa_with_passphrase.pub';
$testParameters["sftp_client"]["dsa_with_passphrase_priv"] = 'Test/SSH-Keys/dsa_with_passphrase';
$testParameters["sftp_client"]["rsa_no_passphrase_pub"]    = 'Test/SSH-Keys/rsa_no_passphrase.pub';
$testParameters["sftp_client"]["rsa_no_passphrase_priv"]   = 'Test/SSH-Keys/rsa_no_passphrase';
$testParameters["sftp_client"]["rsa_with_passphrase_pub"]  = 'Test/SSH-Keys/rsa_with_passphrase.pub';
$testParameters["sftp_client"]["rsa_with_passphrase_priv"] = 'Test/SSH-Keys/rsa_with_passphrase';
$testParameters["sftp_client"]["priv_key_passphrase"]      = 'c94-HGPLM!N13XDSLKH$';
$testParameters["sftp_client"]["inbound_directory"]        = 'Test/IO-Dir/inbound';
$testParameters["sftp_client"]["outbound_directory"]       = 'Test/IO-Dir/outbound';
$testParameters["sftp_client"]["looptest_directory"]       = 'Test/IO-Dir/loop';
$testParameters["sftp_client"]["connection_methods"]       = array('client_to_server' => array('comp' => 'none'),
                                                                   'server_to_client' => array('comp' => 'none'));

// --------------------------------------------------------------------
// MAIN
// --------------------------------------------------------------------
function main($testParams)
{
    $testSftp = new TestSftpClient($testParams);
    //$testSftp->testQueryHostKeyFingerprint();
    //$testSftp->localTestDirFileListing();

    // Test password authentication and directory listing.
    //$testSftp->testList();
    //$testSftp->testListDetails();

    // Test password authentication, file upload, file download,
    // directory listing, and file delete.
    //$testSftp->testAuthUpDownListDel();

    // Test password authentication and file upload.
    //$testSftp->testUpload();

    // Test public key authentication without encrypted private keys
    //$testSftp->testPublicKeyAuthenticationNoPrivKeyPassphrase();

    // Test password and public key authentication (done together)
    //$testSftp->testPasswordAndPublicAuthentication();

    // Test modifying permissions of a file
    $testSftp->testChmod();
    echo "Done.\r\n";
}
main($testParameters);

?>