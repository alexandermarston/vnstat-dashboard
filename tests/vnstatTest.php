<?php
declare(strict_types=1);

use alexandermarston\vnStat;
use PHPUnit\Framework\TestCase;

final class vnStatTest extends TestCase {

    private $vnStat;

    /**
    * Class constructor
    */
    public function testClassCanBeCreatedFromValidExecutablePath(): void {
        $this->vnStat = new vnStat('/usr/bin/vnstat');

        $this->assertInstanceOf('alexandermarston\vnStat', $this->vnStat, 'Class Constructor');
    }

    /**
     * @covers \alexandermarston\vnStat::processVnstatData
     */
    public function testProcessVnstatData(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \alexandermarston\vnStat::getVnstatVersion
     */
    public function testGetVnstatVersion(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \alexandermarston\vnStat::getVnstatJsonVersion
     */
    public function testGetVnstatJsonVersion(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \alexandermarston\vnStat::getInterfaces
     */
    public function testGetInterfaces(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \alexandermarston\vnStat::getInterfaceData
     */
    public function testGetInterfaceData(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

}

?>
