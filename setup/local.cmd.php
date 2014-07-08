<?php
/**
 * @see https://github.com/tokushima/cmdman
 */
if(\cmdman\Std::read('create table','y',['y','n']) == 'y'){
	foreach(\ebi\Dt::create_table() as $model){
		\cmdman\Std::println_primary('Created '.$model[1]);
	}
}
