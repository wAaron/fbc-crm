							<tr>
								<th>
									<?php echo _l('Contact Position'); ?>
								</th>
								<td>
									<input type="text" name="contact_position" style="width: 200px;" value="<?php echo isset($user['contact_position'])? htmlspecialchars($user['contact_position']) : ''; ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Sex'); ?>
								</th>
								<td>
	                                <?php
	                                echo print_select_box_nokey(array("男", "女", "不明"), "contact_sex", isset($user['contact_sex'])? htmlspecialchars($user['contact_sex']) : '' );
	                                ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Company Address'); ?>
								</th>
								<td>
									<input type="text" name="contact_company_addr" style="width: 200px;" value="<?php echo isset($user['contact_company_addr'])? htmlspecialchars($user['contact_company_addr']) : ''; ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Home Address'); ?>
								</th>
								<td>
									<input type="text" name="contact_home_addr" style="width: 200px;" value="<?php echo isset($user['contact_home_addr'])? htmlspecialchars($user['contact_home_addr']) : ''; ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Marriage'); ?>
								</th>
								<td>
	                                <?php
	                                echo print_select_box_nokey(array("已婚", "未婚"), "contact_marriage", isset($user['contact_marriage'])? htmlspecialchars($user['contact_marriage']) : '' );
	                                ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Birthday'); ?>
								</th>
								<td>
                                	<input type="text" name="contact_birthday" id="contact_birthday" class="date_field" value="<?php echo isset($user['contact_birthday'])? htmlspecialchars($user['contact_birthday']) : ''; ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Temper'); ?>
								</th>
								<td>
	                                <?php
	                                echo print_select_box_nokey(array("贪婪", "虚荣", "贪吃", "易怒", "好色", "苛刻", "拖沓", "傲慢", "自负", "狡诈", "嫉妒", "粗心", "固执", "好胜", "宽容", "严谨", "干练", "耐心", "正直"), 
	                                		"contact_temper", isset($user['contact_temper'])? htmlspecialchars($user['contact_temper']) : '' );
	                                ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Education'); ?>
								</th>
								<td>
									<input type="text" name="contact_education" style="width: 200px;" value="<?php echo isset($user['contact_education'])? htmlspecialchars($user['contact_education']) : ''; ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Nationality'); ?>
								</th>
								<td>
									<input type="text" name="contact_nationality" style="width: 200px;" value="<?php echo isset($user['contact_nationality'])? htmlspecialchars($user['contact_nationality']) : ''; ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Jiguan'); ?>
								</th>
								<td>
									<input type="text" name="contact_jiguan" style="width: 200px;" value="<?php echo isset($user['contact_jiguan'])? htmlspecialchars($user['contact_jiguan']) : ''; ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Child Birth Year'); ?>
								</th>
								<td>
	                                <?php
	                                echo print_select_box_nokey(range(1900, 2020), "contact_child_year", isset($user['contact_child_year'])? htmlspecialchars($user['contact_child_year']) : '' );
	                                ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Ethnic'); ?>
								</th>
								<td>
									<input type="text" name="contact_ethnic" style="width: 200px;" value="<?php echo isset($user['contact_ethnic'])? htmlspecialchars($user['contact_ethnic']) : ''; ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact CV'); ?>
								</th>
								<td>
									<input type="text" name="contact_cv" style="width: 200px;" value="<?php echo isset($user['contact_cv'])? htmlspecialchars($user['contact_cv']) : ''; ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Social Network'); ?>
								</th>
								<td>
									<input type="text" name="contact_social_network" style="width: 200px;" value="<?php echo isset($user['contact_social_network'])? htmlspecialchars($user['contact_social_network']) : ''; ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Interest'); ?>
								</th>
								<td>
									<input type="text" name="contact_interest" style="width: 200px;" value="<?php echo isset($user['contact_interest'])? htmlspecialchars($user['contact_interest']) : ''; ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Disease'); ?>
								</th>
								<td>
									<input type="text" name="contact_disease" style="width: 200px;" value="<?php echo isset($user['contact_disease'])? htmlspecialchars($user['contact_disease']) : ''; ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Misc'); ?>
								</th>
								<td>
									<input type="text" name="contact_misc" style="width: 200px;" value="<?php echo isset($user['contact_misc'])? htmlspecialchars($user['contact_misc']) : ''; ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Importance'); ?>
								</th>
								<td>
									<input id="contact_importance" type="hidden" name="contact_importance" value="<?php echo isset($user['contact_importance'])? htmlspecialchars($user['contact_importance']) : '0'; ?>" />
									<div class="contact_star_rate" data-average="12" data-id="1" data-target="contact_importance"></div>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Contact Sensitive'); ?>
								</th>
								<td>
									<input id="contact_sensitive" type="hidden" name="contact_sensitive" value="<?php echo isset($user['contact_sensitive'])? htmlspecialchars($user['contact_sensitive']) : '0'; ?>" />
									<div class="contact_star_rate" data-average="12" data-id="1" data-target="contact_sensitive"></div>
								</td>
							</tr>