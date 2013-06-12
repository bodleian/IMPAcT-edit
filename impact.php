<?php
/*
 * IMPAcT catalogue menu
 * Subversion repository: https://damssupport.bodleian.ox.ac.uk/svn/impact/php
 * Author: Sushila Burgess
 *
 */

define( 'CFG_PREFIX', 'impact' );
define( 'CFG_SYSTEM_TITLE', 'IMPAcT Catalogue Editing Interface' );

define( 'IMPACT_MAIN_SITE', 'http://www.orinst.ox.ac.uk/staff/iw/jpfeiffer.html' );

if( ! $database_type_set ) 
  define( 'CONSTANT_DATABASE_TYPE', 'live' );

if( ! $sourcedir_set )
  define( 'CONSTANT_SOURCEDIR', '/path/ending/in/slash/' );

$include_file = CONSTANT_SOURCEDIR . 'common_components.php';
require_once "$include_file";

$impact = new Application_Entity;
$impact->startup();

?>
