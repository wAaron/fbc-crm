
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form info-{{info.address.hide}}">
	<tbody>
	<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:23:35 
  * IP Address: 210.14.75.228
  */
	$fields = array(
		"line_1" => array(module_config::c('address_1',"Line 1"),30),
		"line_2" => array(module_config::c('address_2',"Line 2"),30),
		"suburb" => array(module_config::c('address_3',"Suburb"),30),
        "state" => array(module_config::c('address_4',"State"),20),
        "region" => array(module_config::c('address_5',"Region"),20),
		"post_code" => array(module_config::c('address_6',"Post Code"),10),
        "country" => array(module_config::c('address_7',"Country"),20),
	);
	foreach($fields as $key=>$val){
	?>
		<tr>
			<th class="width1">
				<?php echo _l($val[0]); ?>
			</th>
			<td>
				<?php
				// quick added hack for 'region' to display as a drop down
				if($key=='region_id' || $key=='state_id' || $key=='country_id'){
					echo print_select_box(get_col_vals('address',$key),'address['.$address_hash.']['.$key.']',(isset($address[$key]) ? $address[$key] : ''),'',false,false,true);
                }else{
                    /*
                    //echo print_select_box(get_col_vals('address_region','region_id','region'),'address['.$address_hash.']['.$key.']',(isset($address[$key]) ? $address[$key] : ''));
                    echo module_config::print_db_select_box(array(
                        'db_table'=>'address_region',
                        'db_key'=>'region_id',
                        'db_val'=>'region',
                        'db_order'=>'region',
                        'fields' => array(
                            'region_id' => array(
                                'size' => 5,
                                'title' => 'Region ID',
                            ),
                            'region' => array(
                                'size' => 20,
                                'title' => 'Region Name',
                            ),
                            'state_id' => array(
                                'size' => 5,
                                'title' => 'State',
                                'attributes' => get_col_vals('address_state','state_id','state'),
                            ),
                        ),
                        'name'=>'address['.$address_hash.']['.$key.']',
                        'val'=>(isset($address[$key]) ? $address[$key] : ''),
                        //'allow_new' => true,
                    ));
				}else if($key=='state_id'){

                    echo module_config::print_db_select_box(array(
                        'db_table'=>'address_state',
						'db_sql' => "SELECT * FROM `"._DB_PREFIX."address_state` s ORDER BY s.country_id, state",
                        'db_key'=>'state_id',
                        'db_val'=>'state',
                        'fields' => array(
                            'state_id' => array(
                                'size' => 5,
                                'title' => 'State ID',
                            ),
                            'state' => array(
                                'size' => 20,
                                'title' => 'State Name',
                            ),
                            'country_id' => array(
                                'size' => 5,
                                'title' => 'Country',
                                'attributes' => get_col_vals('address_country','country_id','country'),
                            ),
                        ),
                        'name'=>'address['.$address_hash.']['.$key.']',
                        'val'=>(isset($address[$key]) ? $address[$key] : ''),
                       // 'allow_new' => true,
                    ));
				}else if($key=='country_id'){
                    echo module_config::print_db_select_box(array(
                        'db_table'=>'address_country',
                        'db_key'=>'country_id',
                        'db_val'=>'country',
                        'db_order'=>'country',
                        'name'=>'address['.$address_hash.']['.$key.']',
                        'val'=>(isset($address[$key]) ? $address[$key] : ''),
                       // 'allow_new' => true,
                    ));
                }else{ */
                    ?>
					<input type="text" name="address[<?php echo $address_hash;?>][<?php echo $key;?>]" value="<?php echo isset($address[$key]) ? htmlspecialchars($address[$key]) : ''; ?>" size="<?php echo $val[1];?>" />
				<?php } ?>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>

    <input type="hidden" name="address[<?php echo $address_hash;?>][address_id]" value="<?php echo $address_id;?>">
<input type="hidden" name="address[<?php echo $address_hash;?>][owner_id]" value="<?php echo $owner_id;?>">
<input type="hidden" name="address[<?php echo $address_hash;?>][owner_table]" value="<?php echo $owner_table;?>">
<input type="hidden" name="address[<?php echo $address_hash;?>][address_type]" value="<?php echo $address_type;?>">