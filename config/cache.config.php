<?php
/**
 * Npds Two
 *
 * Based on NPDS Copyright (c) 2002-2020 by Philippe Brunier
 * 
 * @author Nicolas2
 * @version 1.0
 * @date 02/04/2021
 */

// Be sure that apache user have the permission to Read/Write/Delete in the Dir
$CACHE_CONFIG['data_dir'] = 'storage/cache/';

// How the Auto_Cleanup process is run
// 0 no cleanup - 1 auto_cleanup
$CACHE_CONFIG['run_cleanup']  = 1;

// value between 1 and 100. The most important is the value, the most "probabilidad", cleanup process as chance to be runed
$CACHE_CONFIG['cleanup_freq'] = 20;

// maximum age - 24 Hours
$CACHE_CONFIG['max_age'] = 86400;

// Instant Stats
// 0 no - 1 Yes
$CACHE_CONFIG['save_stats'] = 0;

// Terminate send http process after sending cache page
// 0 no - 1 Yes
$CACHE_CONFIG['exit'] = 0;

// If the maximum number of "webuser" is ritched : SuperCache not clean the cache
// compare with the value store in storage/cache/site_load.log updated by the site_load() function of mainfile.php
$CACHE_CONFIG['clean_limit'] = 300;

// Same standard cache (not the functions for members) for anonymous and members
// 0 no - 1 Yes
$CACHE_CONFIG['non_differentiate'] = 0;

?>