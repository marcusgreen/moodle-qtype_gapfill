<?php
/**
 * Unit tests for this question type.
 *
 * @copyright &copy; 2006 YOURNAME
 * @author YOUREMAILADDRESS
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package YOURPACKAGENAME
 *//** */
    
require_once(dirname(__FILE__) . '/../../../../config.php');

global $CFG;
require_once($CFG->libdir . '/simpletestlib.php');
require_once($CFG->dirroot . '/question/type/QTYPEID/questiontype.php');

class QTYPEID_qtype_test extends UnitTestCase {
    var $qtype;
    
    function setUp() {
        $this->qtype = new QTYPEID_qtype();
    }
    
    function tearDown() {
        $this->qtype = null;    
    }

    function test_name() {
        $this->assertEqual($this->qtype->name(), 'QTYPEID');
    }
    
    // TODO write unit tests for the other methods of the question type class.
}

?>
