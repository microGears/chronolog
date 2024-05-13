<?php
/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 use PHPUnit\Framework\TestCase;
use Chronolog\LogEntity;
use Chronolog\DateTimeStatement;
use Chronolog\Severity;

class LogEntityTest extends TestCase
{
    public function testLogEntityWithAssets()
    {
        // Arrange
        $datetime = new DateTimeStatement('2024-05-06 19:02:15');
        $severity = Severity::Info;
        $message = 'Test message';
        $track = 'test_track';
        $assets = ['key1' => 'value1', 'key2' => 'value2'];

        // Act
        $logEntity = new LogEntity($datetime, $severity, $message, $track, $assets);

        // Assert
        $this->assertEquals($assets, $logEntity->assets);
    }

    public function testLogEntitySpecialCharactersAndSymbols()
    {
        // Arrange
        $datetime = new DateTimeStatement();
        $severity = Severity::Info;
        $message = "This is a test message with special characters: @#$%^&*()";
        $track = "test_track";
        $assets = ['key1' => 'value1', 'key2' => 'value2'];

        // Act
        $logEntity = new LogEntity($datetime, $severity, $message, $track, $assets);

        // Assert
        $this->assertEquals($datetime, $logEntity->datetime);
        $this->assertEquals($severity, $logEntity->severity);
        $this->assertEquals($message, $logEntity->message);
        $this->assertEquals($track, $logEntity->track);
        $this->assertEquals($assets, $logEntity->assets);
        $this->assertTrue($logEntity->relevant);

        // Additional assertions for toArray method
        $logArray = $logEntity->toArray();
        $this->assertEquals($datetime, $logArray['datetime']);
        $this->assertEquals($severity->value, $logArray['severity']);
        $this->assertEquals($severity->getName(), $logArray['severity_name']);
        $this->assertEquals($message, $logArray['message']);
        $this->assertEquals($track, $logArray['track']);
        $this->assertEquals($assets, $logArray['assets']);
        $this->assertEquals($logEntity->relevant, $logArray['relevant']);

        // Additional assertion for fork method
        $forkedLogEntity = $logEntity->fork();
        $this->assertEquals($logEntity, $forkedLogEntity);

        // Additional assertion for clone method
        $clonedLogEntity = LogEntity::clone($logEntity);
        $this->assertEquals($logEntity, $clonedLogEntity);
    }

    public function testLogEntityWithRelevanceFalse()
    {
        // Arrange
        $datetime = new DateTimeStatement('2024-05-06 19:02:15');
        $severity = Severity::Info;
        $message = 'Test message';
        $track = 'test_track';
        $relevant = false;

        // Act
        $logEntity = new LogEntity($datetime, $severity, $message, $track, [], $relevant);

        // Assert
        $this->assertFalse($logEntity->relevant);
    }

    public function testLogEntityToArray()
    {
        // Arrange
        $datetime = new DateTimeStatement('2024-05-06 19:02:15');
        $severity = Severity::Info;
        $message = 'Test message';
        $track = 'test_track';
        $assets = ['key1' => 'value1', 'key2' => 'value2'];
        $relevant = false;

        // Act
        $logEntity = new LogEntity($datetime, $severity, $message, $track, $assets, $relevant);
        $logEntityArray = $logEntity->toArray();

        // Assert
        $this->assertEquals([
            'datetime' => $datetime,
            'severity' => $severity->value,
            'severity_name' => $severity->getName(),
            'message' => $message,
            'track' => $track,
            'assets' => $assets,
            'relevant' => $relevant
        ], $logEntityArray);
    }
    
    public function testLogEntityClone()
    {
        // Arrange
        $datetime = new DateTimeStatement('2024-05-06 19:02:15');
        $severity = Severity::Info;
        $message = 'Test message';
        $track = 'test_track';
        $assets = ['key1' => 'value1', 'key2' => 'value2'];
        $relevant = false;

        // Act
        $logEntity = new LogEntity($datetime, $severity, $message, $track, $assets, $relevant);
        $clonedLogEntity = $logEntity->fork();

        // Assert
        $this->assertEquals($logEntity, $clonedLogEntity);
        $this->assertNotSame($logEntity, $clonedLogEntity);
    }    
}