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
$module->page_title=_l('Customer Signup');
print_heading(array(
                        'title' => 'Customer Signup Form (BETA)',
                        'type' => 'h2',
                    )); ?>

<?php
module_config::print_settings_form(array(
    array(
        'key'=>'customer_signup_allowed',
        'default'=>0,
        'type'=>'select',
        'options' => array(
            0 => _l('Not allowed'),
            1 => _l('Allowed'),
        ),
        'description'=>'Enable customer signup form',
    ),
    array(
        'key'=>'customer_signup_always_new',
        'default'=>0,
        'type'=>'select',
        'options' => array(
            0 => _l('Allow Update of Existing Customer Entries'),
            1 => _l('Always Create New Customer Entries'),
        ),
        'description'=>'Matching email address action',
        'help' =>'If a customer fills in this form and the email address already exists in the system then it can update the existing entry instead of creating a new customer entry. If updating existing entry then the new customer name will be applied, which could differ from existing company name. Set this option to "Always Create New Customer Entry" if you do not want a customer to be able to update their existing details.',
    ),
    array(
        'key'=>'captcha_on_signup_form',
        'default'=>0,
        'type'=>'select',
        'options' => array(
            0 => _l('No'),
            1 => _l('Yes'),
        ),
        'description'=>'Use CAPTCHA on signup form',
    ),
));
?>

<?php ob_start(); ?>
<style type="text/css">
#ucmsignup fieldset {
    padding: 0;
    margin-bottom: 10px;
    border:none;
    border-top: 1px solid #CCC;
}
#ucmsignup legend {
  padding: 0 2px;
  font-weight: bold;
}
#ucmsignup label {
  display: inline-block;
  line-height: 1.8;
  vertical-align: top;
}
#ucmsignup fieldset ol {
  margin: 0;
  padding: 0;
}
#ucmsignup fieldset li {
  list-style: none;
  padding: 5px;
  margin: 0;
}
#ucmsignup fieldset fieldset {
  border: none;
  margin: 3px 0 0;
}
#ucmsignup fieldset fieldset legend {
  padding: 0 0 5px;
  font-weight: normal;
}
#ucmsignup fieldset fieldset label {
  display: block;
  width: auto;
}
#ucmsignup em {
  font-weight: bold;
  font-style: normal;
  color: #f00;
}
#ucmsignup label {
  width: 120px; /* Width of labels */
}
#ucmsignup fieldset fieldset label {
  margin-left: 123px; /* Width plus 3 (html space) */
}
#ucmsignup .required{
    color:#CCC;
}
#ucmsignup input,
#ucmsignup select,
#ucmsignup textarea {
    background-color: #F8F8F8;
    border: 1px solid #CCC;
    font-family: inherit;
    font-size: 12px;
    padding: 1px;
    margin: 0;
</style>
<form action="<?php echo module_customer::link_public_signup();?>" enctype="multipart/form-data" method="post">
<div id="ucmsignup">
<fieldset>
<legend>Customer Information</legend>
<ol>
    <li>
        <label for="customer[name]">First Name <span class="required">*</span></label>
        <input id="customer[name]" name="customer[name]" />
    </li>
    <li>
        <label for="customer[last_name]">Last Name</label>
        <input id="customer[last_name]" name="customer[last_name]" />
    </li>
    <li>
        <label for="customer[customer_name]">Company Name </label>
        <input id="customer[customer_name]" name="customer[customer_name]" />
    </li>
    <li>
        <label for="customer[email]">Email Address <span class="required">*</span></label>
        <input id="customer[email]" name="customer[email]" />
    </li>
    <li>
        <label for="customer[phone]">Phone Number</label>
        <input id="customer[phone]" name="customer[phone]" />
    </li>
    <?php
    $x=1;
    foreach(module_extra::get_defaults('customer') as $default){ ?>
    <li>
        <label for="customer_extra_<?php echo $x;?>"><?php echo htmlspecialchars($default['key']);?></label>
        <input id="customer_extra_<?php echo $x;?>" name="customer[extra][<?php echo htmlspecialchars($default['key']);?>]" />
    </li>
    <?php $x++;
    } ?> 
</ol>
</fieldset>
<fieldset>
<legend>Address</legend>
<ol>
    <li>
        <label for="address[line_1]">Address (Line 1) </label>
        <input id="address[line_1]" name="address[line_1]" />
    </li>
    <li>
        <label for="address[line_2]">Address (Line 2) </label>
        <input id="address[line_2]" name="address[line_2]" />
    </li>
    <li>
        <label for="address[suburb]">Suburb</label>
        <input id="address[suburb]" name="address[suburb]" />
    </li>
    <li>
        <label for="address[state]">State</label>
        <input id="address[state]" name="address[state]" />
    </li>
    <li>
        <label for="address[region]">Region</label>
        <input id="address[region]" name="address[region]" />
    </li>
    <li>
        <label for="address[post_code]">Post Code</label>
        <input id="address[post_code]" name="address[post_code]" />
    </li>
    <li>
        <label for="address[country]">Country</label>
        <input id="address[country]" name="address[country]" />
    </li>
</ol>
</fieldset>
<fieldset>
<legend>Project Details</legend>
<ol>
    <li>
        <label for="website[url]">Website Address</label>
        <input id="website[url]" name="website[url]" />
    </li>
    <li>
        <fieldset>
            <legend>Which of the below services do you require? <span class="required">*</span></legend>
            <?php foreach(module_job::get_types() as $type_id => $type){ ?>
            <label><input type="checkbox" name="job[type][<?php echo htmlspecialchars($type_id);?>]" value="<?php echo htmlspecialchars($type);?>" /> <?php echo htmlspecialchars($type);?></label>
            <?php } ?>
        </fieldset>
    </li>
    <li>
        <fieldset>
            <legend>Please upload any attachments for this project below:</legend>
            <label><input type="file" name="customerfiles[]" value=""></label>
            <label><input type="file" name="customerfiles[]" value=""></label>
            <label><input type="file" name="customerfiles[]" value=""></label>
            <label><input type="file" name="customerfiles[]" value=""></label>
            <!-- add more files here by simply duplicating a line above -->
        </fieldset>
    </li>
    <?php
    $x=1;
    foreach(module_extra::get_defaults('website') as $default){ ?>
    <li>
        <label for="website_extra_<?php echo $x;?>"><?php echo htmlspecialchars($default['key']);?></label>
        <input id="website_extra_<?php echo $x;?>" name="website[extra][<?php echo htmlspecialchars($default['key']);?>]" />
    </li>
    <?php $x++;
    } ?>
    <?php
    /*$x=1;
    foreach(module_extra::get_defaults('job') as $default){ ?>
    <li>
        <label for="job_extra_<?php echo $x;?>"><?php echo htmlspecialchars($default['key']);?></label>
        <input id="job_extra_<?php echo $x;?>" name="job[extra][<?php echo htmlspecialchars($default['key']);?>]" />
    </li>
    <?php $x++;
    }*/ ?>
    <li>
        <label for="website[notes]">Comments</label>
        <textarea id="website[notes]" name="website[notes]" rows="7" cols="25"></textarea>
    </li>
</ol>
</fieldset>
    <?php if(module_config::c('captcha_on_signup_form',0)){ ?>
    <fieldset>
        <legend>Spam Prevention</legend>
        <?php module_captcha::display_captcha_form(); ?>
    </fieldset>
    <?php } ?>
    <p><input type="submit" value="Signup Now" /></p>
</div>
</form>
<?php $form_html = ob_get_clean(); ?>

<table width="100%">
    <tbody>
    <tr>
        <td valign="top" width="50%">
            <?php echo $form_html;?>
        </td>
        <td valign="top">
            <p>
                On the left is an example signup form - your customers can complete this form to input their details directly into your system - handy! <br> You can copy &amp; paste the HTML code onto your website.
                You can adjust this HTML to suit your needs - you can even remove all the fields except the required ones.
                As long as the field names are kept the same as they are now you will be fine.
            </p>
            <p>
                The best way to see how this works is to fill in the example form on the left - then have a look through your system to see how that information is inserted. Below is the information that is inserted into the system from this form:

            </p>
            <ul>
                <li>A Customer with the customer name from the form (new or existing customer, depending on the 'matching' setting above)</li>
                <li>A Customer Contact with the name, email and phone number from the form</li>
                <li>A new Website linked to the Customer, the "notes" will be added to this website entry.</li>
                <li>A new Job linked to the Customer for each "service" that is ticked in the form.</li>
                <li>Any files will be uploaded and linked to the Customer account.</li>
                <li>If there are any "custom" fields for the Customer or Website you will see them here in the form. These also get added to the system respectively. After adding new custom fields please come back here to regenerate the HTML code for your website.</li>
                <li>An email will be sent to the customer (see Settings > Template to configure this email)</li>
                <li>An email will be sent to the administrator with details of the signup (see Settings > Template to configure this email)</li>
                <li>A thank you message will be displayed after submitting the form (see Settings > Template to configure this message)</li>
            </ul>
            <p>
                Below is the HTML code for the form on the left, including some sample styles which you can adjust to match your own website.
            </p>
            <textarea rows="3" cols="3" style="width:90%; height: 500px"><?php echo htmlspecialchars($form_html);?></textarea>
        </td>
    </tr>
    </tbody>
</table>