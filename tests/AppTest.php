<?php
class AppTest extends PHPUnit_Framework_TestCase {
    
    /*private
        $db;

    public function setUp () {
        $this->db = new \Gajus\Doll\PDO(new \Gajus\Doll\DataSource([
            'username' => 'travis',
            'database' => 'doll'
        ]));
    }

    public function testDefaultToNoLogging () {
        $this->assertFalse($this->db->getAttribute(\Gajus\Doll\PDO::ATTR_LOGGING));
    }

    public function testEnableLogging () {
        $this->db->setAttribute(\Gajus\Doll\PDO::ATTR_LOGGING, true);

        $this->assertTrue($this->db->getAttribute(\Gajus\Doll\PDO::ATTR_LOGGING));
    }

    public function testLogFormat () {
        $this->db->setAttribute(\Gajus\Doll\PDO::ATTR_LOGGING, true);

        $sth = $this->db->prepare("SELECT :foo");

        $sth->execute(['foo' => 1]);

        $log = $this->db->getLog();

        $this->assertCount(1, $log);

        $log[0]['execution_wall_time'] = 0;
        $log[0]['execution_duration'] = 0;
        $log[0]['execution_overhead'] = 0;

        $this->assertSame([
            'statement' => 'SELECT :foo',
            'parameters' => [
                'foo' => 1
            ],
            'execution_wall_time' => 0,
            'backtrace' => [
                'file' => __FILE__,
                'line' => __LINE__ - 18,
                'function' => 'execute',
                'class' => 'Gajus\Doll\PDOStatement',
                'type' => '->'
            ],
            'execution_duration' => 0,
            'execution_overhead' => 0,
            'query' => 'SELECT ?'
        ], $log[0]);
    }

    public function testLogEachStatementExecution () {
        $this->db->setAttribute(\Gajus\Doll\PDO::ATTR_LOGGING, true);

        $sth = $this->db->prepare("SELECT 1");

        $sth->execute();
        $sth->execute();

        $log = $this->db->getLog();

        $this->assertCount(2, $log);
    }

    public function testDoNotLogStatementExecutionWhenLoggingIsNotEnabled () {
        $sth = $this->db->prepare("SELECT 1");

        $sth->execute();
        $sth->execute();

        $log = $this->db->getLog();

        $this->assertCount(0, $log);
    }

    public function testDoNotLogNotExecutedStatement () {
        $sth = $this->db->prepare("SELECT 1");

        $this->assertCount(0, $this->db->getLog());
    }*/
}