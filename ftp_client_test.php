<?php

require_once "lib/FtpClient.php";
require_once "Test/lib/TestFtpClient.php";


// ---------------------------------------------------------------------------
// MAIN
// ---------------------------------------------------------------------------

$localFilename = "ORDERS05_EDIADMIN.sap";
$remoteFilename = "ORDERS05_EDIADMIN.sap";


$configFilePath = "../ftp_config.ini";
$configuration = parse_ini_file($configFilePath, TRUE);

$ftpNormalHostname = $configuration["ftp_normal"]["hostname"];
$ftpNormalHostport = $configuration["ftp_normal"]["port"];
$ftpNormalUsername = $configuration["ftp_normal"]["username"];
$ftpNormalPassword = $configuration["ftp_normal"]["password"];

$ftpAccountHostname = $configuration["ftp_account"]["hostname"];
$ftpAccountHostport = $configuration["ftp_account"]["port"];
$ftpAccountUsername = $configuration["ftp_account"]["username"];
$ftpAccountPassword = $configuration["ftp_account"]["password"];
$ftpAccountAccount  = $configuration["ftp_account"]["account"];

$ftpClient = New FtpClient();
$testFtpClient = New TestFtpClient($ftpClient);

$passive = TRUE;

$testFtpClient->testAuthNormalSecure ($ftpNormalHostname, $ftpNormalHostport, $ftpNormalUsername, $ftpNormalPassword);
//$testFtpClient->testAuthAccountSecure ($ftpAccountHostname, $ftpAccountHostport, $ftpAccountUsername, $ftpAccountPassword, $ftpAccountAccount);
$testFtpClient->testUsePassive($passive);
$currentWorkingDir = $testFtpClient->testPwd();
$rawDirListing = $testFtpClient->testListRaw($currentWorkingDir);
var_dump($rawDirListing);
if($testFtpClient->testUploadFile($remoteFilename, $localFilename))
{
    print("File upload test successful.\r\n");
    $currentWorkingDir = $testFtpClient->testPwd();
    $rawDirListing = $testFtpClient->testListRaw("/*.sap");
    var_dump($rawDirListing);
}

if($testFtpClient->testDownloadFile($remoteFilename, $localFilename))
{
    print("File download test successful.\r\n");
    $currentWorkingDir = $testFtpClient->testPwd();
    $rawDirListing = $testFtpClient->testListRaw("/*.sap");
    var_dump($rawDirListing);
}

if($testFtpClient->testDeleteFile($remoteFilename))
{
    print("Successfully deleted file from FTP server.\r\n");
    $currentWorkingDir = $testFtpClient->testPwd();
    $rawDirListing = $testFtpClient->testListRaw("/*.sap");
    var_dump($rawDirListing);
}


?>