<?php
/**
 * THIS SOFTWARE IS PROVIDED "AS IS" AND ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING,
 * BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
 * PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage app
 */

/**
 * The Executive class is responsible for executing the remote service method and returning it's value.
 * 
 * Currently the executive class is a complicated chain of filtering events testing for various cases and
 * handling them.  Future versions of this class will probably be broken up into many helper classes which will
 * use a delegation or chaining pattern to make adding new exceptions or handlers more modular.  This will
 * become even more important if developers need to make their own custom header handlers.
 * 
 * @package flashservices
 * @subpackage app
 * @author Musicman original design 
 * @author Justin Watkins Gateway architecture, class structure, datatype io additions 
 * @author John Cowen Datatype io additions, class structure 
 * @author Klaasjan Tukker Modifications, check routines 
 * @version $Id: Executive.php,v 1.32 2005/07/05 07:40:49 pmineault Exp $
 */
 


class Executive {
	/**
	 * The built instance of the service class
	 * 
	 * @access private 
	 * @var object 
	 */
	var $_classConstruct;

	/**
	 * The method name to execute
	 * 
	 * @access private 
	 * @var string 
	 */
	var $_methodname;

	/**
	 * The arguments to pass to the executed method
	 * 
	 * @access private 
	 * @var mixed 
	 */
	var $_arguments;

	function Executive() {
	} 

	/**
	 * The main method of the executive class.
	 * 
	 * @param array $a Arguments to pass to the method
	 * @return mixed The results from the method operation
	 */
	function doMethodCall(&$bodyObj, &$object, $method, $args) {
		/* 
		::TODO:: 
		Add the ability to use an object with named parameters as the first and only argument.  This will REQUIRE
		that the arguments are defined in the method table, but that is just the sacrifice that will have to be made.
		
		Maybe the arguments should be it's own action or bundled with another action...
		*/
		//Unfortunately there is no way to catch (as of PHP 4) an error that occurs in a user function
		//You will receive a bad version error if that is the case
		//You will have to rely on server logs for this purpose
		return call_user_func_array (array(&$object, $method), $args);
	} 
	
	/**
	 * Builds a class using a class name
	 * If there is a failure, catch the error and return to caller
	 */
	function buildClass(&$bodyObj, $className)
	{
		global $amfphp;
		if(isset($amfphp['classInstances'][$className]))
		{
			return $amfphp['classInstances'][$className];
		}
		else
		{
			$construct = new $className($className);
			$amfphp['classInstances'][$className] = & $construct;
			return $construct;
		}
	}
	
	/**
	 * Include a class
	 * If there is an error, catch and return to caller
	 */
	function includeClass(&$bodyObj, $location)
	{
		$included = include_once($location);
		return $included !== FALSE;
	}
} 

?>
