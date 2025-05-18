<?php
// cleanUp.php: A background script to perform periodic clean-up tasks

require_once __DIR__ . '/../src/ExampleService.php';

try {
    // Log the start of the cron task
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Cron job started\n";
    file_put_contents(__DIR__ . '/cron.log', $logMessage, FILE_APPEND);

    // Instantiate the service
    $service = new ExampleService();
    
    // Example: Create the table if it doesn't exist
    $service->createExampleTable();
    
    // Add your cleanup logic here
    // For example: Remove old records, temporary files, etc.
    
    // Log successful completion
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Cron job finished successfully\n";
    file_put_contents(__DIR__ . '/cron.log', $logMessage, FILE_APPEND);
    
} catch (Exception $e) {
    // Log any errors
    $errorMessage = "[" . date('Y-m-d H:i:s') . "] Cron job failed: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . '/cron.log', $errorMessage, FILE_APPEND);
    exit(1);
}
?>
