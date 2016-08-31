<link media="screen" href="/templates/{THEME}/billing/css/styles.css" type="text/css" rel="stylesheet" />

<script type="text/javascript">
	var bs_lang_error = "Внимание";
	var bs_lang_sum = "Введите корректную сумму";
	var bs_lang_select = "Выберите один из предложенных способов оплаты счёта.";
	var bs_lang_minimum = "Минимум для оплаты через платёжную систему ";
	var bs_lang_maximum = "Максимум для оплаты через платёжную систему ";
</script>

<script type="text/javascript" src="/templates/{THEME}/billing/js/scripts.js"></script>
<script src="/templates/{THEME}/billing/js/jsmask/jquery.inputmask.js" type="text/javascript"></script>

<div style="text-align: center">
	<ul class="bt_menu_li">
	   <li class="bt_menu_lisel[active]pay[/active]"><a href="/{CABINET}.html/pay/">Пополнить баланс</a></li>
	   <li class="bt_menu_lisel[active]log[/active]"><a href="/{CABINET}.html/log/">История движения средств</a></li>
	   [plugin]<li class="{plugin_active}"><a href="/{CABINET}.html/{plugin_link}/">{plugin_name}</a></li>[/plugin]
	</ul>
</div>

{content}