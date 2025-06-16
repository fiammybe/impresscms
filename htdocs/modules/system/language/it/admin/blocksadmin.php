<?php
// $Id: blocksadmin.php 11329 2011-08-28 11:04:24Z phoenyx $
//%%%%%%	Admin Module Name  Blocks 	%%%%%
if (!defined('_AM_DBUPDATED')) {if (!defined('_AM_DBUPDATED')) {define("_AM_DBUPDATED","Database aggiornato con successo!");}}

//%%%%%%	blocks.php 	%%%%%
define("_AM_BADMIN","Blocks Administration");

# Adding dynamic block area/position system - TheRpLima - 2007-10-21
define('_AM_BPADMIN',"Block Positions Administration");

define("_AM_ADDBLOCK","Add a new block");
define("_AM_LISTBLOCK","List all blocks");
define("_AM_SIDE","Side");
define("_AM_BLKDESC","Block Description");
define("_AM_TITLE","Titolo");
define("_AM_WEIGHT","Weight");
define("_AM_ACTION","Action");
define("_AM_BLKTYPE","Block Type");
define("_AM_LEFT","Sinistra");
define("_AM_RIGHT","Destra");
define("_AM_CENTER","Centro");
define("_AM_VISIBLE","Visibile");
define("_AM_POSCONTT","Position of the additional content");
define("_AM_ABOVEORG","Above the original content");
define("_AM_AFTERORG","After the original content");
define("_AM_EDIT","Modifica");
define("_AM_DELETE","Elimina");
define("_AM_SBLEFT","Side Block - Left");
define("_AM_SBRIGHT","Side Block - Right");
define("_AM_CBLEFT","Center Block - Left");
define("_AM_CBRIGHT","Center Block - Right");
define("_AM_CBCENTER","Center Block - Center");
define("_AM_CBBOTTOMLEFT","Center Block - Bottom left");
define("_AM_CBBOTTOMRIGHT","Center Block - Bottom right");
define("_AM_CBBOTTOM","Center Block - Bottom");
define("_AM_CONTENT","Content");
define("_AM_OPTIONS","Opzioni di notifica");
define("_AM_CTYPE","Content Type");
define("_AM_HTML","HTML");
define("_AM_PHP","PHP Script");
define("_AM_AFWSMILE","Auto Format (smilies enabled)");
define("_AM_AFNOSMILE","Auto Format (smilies disabled)");
define("_AM_SUBMIT","Invia");
define("_AM_CUSTOMHTML","Custom Block (HTML)");
define("_AM_CUSTOMPHP","Custom Block (PHP)");
define("_AM_CUSTOMSMILE","Custom Block (Auto Format + smilies)");
define("_AM_CUSTOMNOSMILE","Custom Block (Auto Format)");
define("_AM_DISPRIGHT","Display only rightblocks");
define("_AM_SAVECHANGES","Salva le modifiche");
define("_AM_EDITBLOCK","Edit a block");
define("_AM_SYSTEMCANT","System blocks cannot be deleted!");
define("_AM_MODULECANT","This block cannot be deleted directly! If you wish to disable this block, deactivate the module.");
define("_AM_RUSUREDEL","Are you sure you want to delete block '%s'?");
define("_AM_NAME","Nome");
define("_AM_USEFULTAGS","Useful Tags:");
define("_AM_BLOCKTAG1","%s will print %s");
define('_AM_SVISIBLEIN', 'Show blocks visible in %s');
define('_AM_TOPPAGE', 'Top Page');
define('_AM_VISIBLEIN', 'Visible in');
define('_AM_ALLPAGES', 'All Pages');
define('_AM_TOPONLY', 'Top Page Only');
define('_AM_ADVANCED', 'Advanced Settings');
define('_AM_BCACHETIME', 'Cache lifetime');
define('_AM_BALIAS', 'Alias name');
define('_AM_CLONE', 'Clona');  // clone a block
define('_AM_CLONEBLK', 'Clona'); // cloned block
define('_AM_CLONEBLOCK', 'Create a clone block');
define('_AM_NOTSELNG', "'%s' is not selected!"); // error message
define('_AM_EDITTPL', 'Edit Template');
define('_AM_MODULE', 'Modulo');
define('_AM_GROUP', 'Gruppo');
define('_AM_UNASSIGNED', 'Unassigned');

define('_AM_CHANGESTS', 'Change the block visibility');

######################## Added in 1.2 ###################################
define('_AM_BLOCKS_PERMGROUPS','Groups allowed to view this block');

/**
 * The next Language definitions are included since 2.0 of blockadmin module, because now is based on IPF.
 * TODO: Add the rest of the fields, are added only the ones which are shown.
 */
// Texts

// Actions
define("_AM_SYSTEM_BLOCKSADMIN_CREATE", "Create a New Block");
define("_AM_SYSTEM_BLOCKSADMIN_EDIT", "Edit a Block");
define("_AM_SYSTEM_BLOCKSADMIN_MODIFIED", "Block Modified Succesfully!");
define("_AM_SYSTEM_BLOCKSADMIN_CREATED", "Block Created Succesfully!");

// Fields
define("_CO_SYSTEM_BLOCKSADMIN_NAME", "Nome");
define("_CO_SYSTEM_BLOCKSADMIN_NAME_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_TITLE", "Titolo");
define("_CO_SYSTEM_BLOCKSADMIN_TITLE_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_MID", "Modulo");
define("_CO_SYSTEM_BLOCKSADMIN_MID_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_VISIBLE", "Visibile");
define("_CO_SYSTEM_BLOCKSADMIN_VISIBLE_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_CONTENT", "Content");
define("_CO_SYSTEM_BLOCKSADMIN_CONTENT_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_SIDE", "Side");
define("_CO_SYSTEM_BLOCKSADMIN_SIDE_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_WEIGHT", "Weight");
define("_CO_SYSTEM_BLOCKSADMIN_WEIGHT_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_BLOCK_TYPE", "Block Type");
define("_CO_SYSTEM_BLOCKSADMIN_BLOCK_TYPE_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_C_TYPE", "Content Type");
define("_CO_SYSTEM_BLOCKSADMIN_C_TYPE_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_OPTIONS", "Opzioni di notifica");
define("_CO_SYSTEM_BLOCKSADMIN_OPTIONS_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_BCACHETIME", "Block Cache Time");
define("_CO_SYSTEM_BLOCKSADMIN_BCACHETIME_DSC", "");

define("_CO_SYSTEM_BLOCKSADMIN_BLOCKRIGHTS", "Block View permission");
define("_CO_SYSTEM_BLOCKSADMIN_BLOCKRIGHTS_DSC", "");

define("_AM_SBLEFT_ADMIN","Admin Side Block - Left");
define("_AM_SBRIGHT_ADMIN","Admin Side Block - Right");
define("_AM_CBLEFT_ADMIN","Admin Center Block - Left");
define("_AM_CBRIGHT_ADMIN","Admin Center Block - Right");
define("_AM_CBCENTER_ADMIN","Admin Center Block - Center");
define("_AM_CBBOTTOMLEFT_ADMIN","Admin Center Block - Bottom left");
define("_AM_CBBOTTOMRIGHT_ADMIN","Admin Center Block - Bottom right");
define("_AM_CBBOTTOM_ADMIN","Admin Center Block - Bottom");
?>