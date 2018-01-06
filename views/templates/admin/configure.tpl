{*
 * 2017 PHPIST
 *
 * NOTICE OF LICENSE
 *
 *  @author    Yassine Belkaid <yassine.belkaid87@gmail.com>
 *  @copyright 2017 PHPIST
 *  @license   MIT License
 *}

<div class="panel">
	<h3><i class="icon icon-credit-card"></i> {l s='PHPIST Customer Registration Blocker' mod='phpistcustomerregistrationblocker'}</h3>
	<p>
		<strong>{l s='Current version' mod='phpistcustomerregistrationblocker'}: {$current_version|escape:'htmlall':'UTF-8'}</strong>
	</p>
	<p>
		<strong>{l s='Author email' mod='phpistcustomerregistrationblocker'}: <em>yassine.belkaid87@gmail.com</em></strong>
	</p>
</div>

<div class="panel">
	<h3><i class="icon icon-tags"></i> {l s='Documentation' mod='phpistcustomerregistrationblocker'}</h3>
	<p>
		&raquo; {l s='You can get a PDF documentation to configure this module' mod='phpistcustomerregistrationblocker'} :
		<ul>
			<li><a href="{$module_dir|escape:'htmlall':'UTF-8'}/readme_en.pdf" target="_blank">{l s='English' mod='phpistcustomerregistrationblocker'}</a></li>
			<li><a href="{$module_dir|escape:'htmlall':'UTF-8'}/readme_fr.pdf" target="_blank">{l s='French' mod='phpistcustomerregistrationblocker'}</a></li>
		</ul>
	</p>
</div>

<script>
$(document).ready(function() {
	var ageInput 	= $('#PHPIST_AGE_BLOCKER');
	var warningMsg 	= "{l s='Only numbers allowed' mod='phpistcustomerregistrationblocker' js=1}";
	var emptyFormMsg= "{l s='Please enter an age' mod='phpistcustomerregistrationblocker' js=1}";

	// add max length attribute
	ageInput.attr("maxlength", 2);

	ageInput.on('keyup', function() {
		if (isNaN($(this).val()) == true) {
			$(this).val("");
			alert(warningMsg);
			return false;
		}
	});

	$('#phpistcustomerregistrationblockersubmit').submit(function(e) {
		e.preventDefault();

		if (ageInput.val() == "") {
			alert(emptyFormMsg);
			return false;
		}
	});
});

</script>