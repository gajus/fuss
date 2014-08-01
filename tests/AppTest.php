<?php
class AppTest extends PHPUnit_Framework_TestCase {
    private
        /**
         * @var Gajus\Puss\App
         */
        $app;


    public function setUp () {
        $this->app = new Gajus\Puss\App(820202914671347, 'a81411f4d1f8a341c8a97cc7d440c7d0');
    }

    public function testParseInvalidSignedRequest () {
        $signed_request = $this->app->parseSignedRequest('1NmO-EbScdWkvTGHfo-QcdpgrKL7lAVjw6WAXh87BZM.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQwNjg5MjI3NSwicGFnZSI6eyJpZCI6IjE0MjY2Mjk0MjQ3NDY4NCIsImxpa2VkIjp0cnVlLCJhZG1pbiI6dHJ1ZX0sInVzZXIiOnsiY291bnRyeSI6Imx0IiwibG9jYWxlIjoiZW5fVVMiLCJhZ2UiOnsibWluIjoyMX19fQ');

        die(var_dump( $signed_request ));
    }

    //2NmO-EbScdWkvTGHfo-QcdpgrKL7lAVjw6WAXh87BZM.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQwNjg5MjI3NSwicGFnZSI6eyJpZCI6IjE0MjY2Mjk0MjQ3NDY4NCIsImxpa2VkIjp0cnVlLCJhZG1pbiI6dHJ1ZX0sInVzZXIiOnsiY291bnRyeSI6Imx0IiwibG9jYWxlIjoiZW5fVVMiLCJhZ2UiOnsibWluIjoyMX19fQ

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