<?php
declare(strict_types=1);

use alexandermarston\vnStat;
use PHPUnit\Framework\TestCase;

final class vnStatTest extends TestCase {

    /**
    * Class constructor
    */
    public function testClassCanBeCreatedFromValidExecutablePath(): void {
        $object = new vnStat('/usr/bin/vnstat');

        $this->assertInstanceOf('alexandermarston\vnStat', $object, 'Class Constructor');
    }

}

?>
