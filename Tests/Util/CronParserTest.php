<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Tests\Util;

use \PHPUnit_Framework_TestCase;
use NineThousand\Jobqueue\Util\CronParser;
use \DateTime;

/**
 * Cron parser test
 */
class CronParserTest extends PHPUnit_Framework_TestCase
{
  /**
   * @covers CronParser::__construct
   * @expectedException InvalidArgumentException
   */
  public function testConstructException()
  {
    $cron = new CronParser('* * * 1');
  }

  /**
   * @covers CronParser::__construct
   * @covers CronParser::getSchedule
   */
  public function testGetSchedule()
  {
    $cron = new CronParser('1 2-4 * 4 */3', '2010-09-10 12:00:00');
    $this->assertEquals('1', $cron->getSchedule('i'));
    $this->assertEquals('2-4', $cron->getSchedule('H'));
    $this->assertEquals('*', $cron->getSchedule('d'));
    $this->assertEquals('4', $cron->getSchedule('m'));
    $this->assertEquals('*/3', $cron->getSchedule('N'));
    $this->assertEquals('1 2-4 * 4 */3', $cron->getSchedule());
  }

  /**
   * Data provider for cron schedule
   *
   * @return array
   */
  public function scheduleProvider()
  {
    return array(
      // schedule,                current time,          last run,              next run,              is due

      // every 2 minutes, every 2 hours
      array('*/2 */2 * * *',     '2010-08-10 21:47:27', '2010-08-10 15:30:00', '2010-08-10 22:00:00', true),
      // every 5 minutes
      array('*/5 * * * *',       '2010-08-10 15:47:27', '2010-08-10 15:45:00', '2010-08-10 15:50:00', false),
      array('*/5 * * * *',       '2010-08-10 15:50:43', '2010-08-10 15:45:00', '2010-08-10 15:55:00', true),
      // every minute
      array('* * * * *',         '2010-08-10 21:50:37', '2010-08-10 21:00:00', '2010-08-10 21:51:00', true),
      array('* * * * *',         '2011-02-02 22:15:52', '2011-02-02 22:15:44', '2011-02-02 22:16:00', false),
      // every day at 16:00
      array('0 16 * * *',        '2010-08-10 15:50:37', '2010-08-09 16:00:34', '2010-08-10 16:00:00', false),
      array('0 16 * * *',        '2010-08-10 16:00:43', '2010-08-09 16:00:34', '2010-08-11 16:00:00', true),
      // Minutes 7-9, every 9 days
      array('7-9 * */9 * *',     '2010-08-10 22:02:33', '2010-08-10 22:01:33', '2010-08-18 00:07:00', false),
      // Minutes 12-19, every 3 hours, every 5 days, in June, on Sunday
      array('12-19 */3 */5 6 7', '2010-08-10 22:05:51', '2010-08-10 22:04:51', '2011-06-05 00:12:00', false),
      // 15th minute, of the second hour, every 15 days, in January, every Friday
      array('15 2 */15 1 */5',   '2010-08-10 22:10:19', '2010-08-10 22:09:19', '2015-01-30 02:15:00', false),
      // 15th minute, of the second hour, every 15 days, in January, Tuesday-Friday
      array('15 2 */15 1 2-5',   '2010-08-10 22:10:19', '2010-08-10 22:09:19', '2013-01-15 02:15:00', false),

      // 9th of month at 2:00am
      array('0 2 9 * *',         '2011-04-12 15:17:28', '2011-04-12 02:01:04', '2011-05-09 02:00:00', false),
      array('0 2 9 * *',         '2011-05-09 02:00:00', '2011-04-12 02:01:04', '2011-05-09 02:00:00', true),

      // check in the minute of next run
      // The difference: The first one is due, it has to run right now!
      //                 The second one has been processed for the current minute.
      array('50 * * * *',        '2010-08-10 20:50:39', '2010-08-10 19:50:23', '2010-08-10 21:50:00', true),
      array('50 * * * *',        '2010-08-10 20:50:56', '2010-08-10 20:50:43', '2010-08-10 21:50:00', false),

      // given by third party to check against
      array('* * * * *',         '2011-02-12 23:33:47', '2011-02-12 23:34:00', '2011-02-12 23:35:00', false),

      // using relative test
      array('* * * * *', date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('-10 minutes')), date('Y-m-d H:i:00', strtotime('+1 minute')), true),
      array('* * * * *', date('Y-m-d H:i:s'), date('Y-m-d H:i:00'), date('Y-m-d H:i:00', strtotime('+1 minute')), false),
    );
  }

  /**
   * @covers CronParser::isDue
   * @covers CronParser::getNextScheduledDate
   * @dataProvider scheduleProvider
   */
  public function testIsDueNextRun($schedule, $currentTime, $lastRun, $nextRun, $isDue)
  {
    $lastRun = new DateTime($lastRun);
    $currentTime = new DateTime($currentTime);
    $nextRun = new DateTime($nextRun);

    $cron = new CronParser($schedule);

    $this->assertEquals($isDue, $cron->isDue($lastRun, $currentTime));
    $this->assertEquals($nextRun, $cron->getNextScheduledDate($lastRun, $currentTime));
  }
}
