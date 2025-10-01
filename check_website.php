<?php

const WEBSITE_URL = 'https://hsctvn.com';
const SUPERVISOR_PROGRAM = 'laravel-queue:*';

function writeLog($message, $level = 'INFO') {
    $date = date('Y-m-d H:i:s');
    $log = "[$date] $level: $message" . PHP_EOL;
    file_put_contents('/www/wwwroot/hoptackinhdoanh.com/storage/logs/website_checker.log', $log, FILE_APPEND);
}

function checkWebsite() {
    $ch = curl_init(WEBSITE_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        writeLog("Lỗi khi kiểm tra website " . WEBSITE_URL . ": $error", 'ERROR');
        curl_close($ch);
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpCode >= 200 && $httpCode < 400;
}

function supervisorAction($action) {
    $cmd = "supervisorctl $action " . SUPERVISOR_PROGRAM;
    exec($cmd . ' 2>&1', $output, $returnCode);
    if ($returnCode === 0) {
        writeLog("Thành công: $action " . SUPERVISOR_PROGRAM);
        return true;
    } else {
        writeLog("Lỗi khi thực hiện $action " . SUPERVISOR_PROGRAM . ": " . implode(', ', $output), 'ERROR');
        return false;
    }
}

function main() {
    $isWebsiteActive = checkWebsite();
    writeLog("Website " . WEBSITE_URL . ($isWebsiteActive ? " đang hoạt động." : " không hoạt động."), $isWebsiteActive ? 'INFO' : 'WARNING');

    if ($isWebsiteActive) {
        supervisorAction('restart');
    } else {
        supervisorAction('stop');
    }
}

main();