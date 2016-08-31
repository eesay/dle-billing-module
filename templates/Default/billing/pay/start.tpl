<form action="" onsubmit="return bs_pay(this)" name="payform" method="post">

	<div class="bt_title">Введите сумму</div>
		
		<div class="bt_panel">
				Пополнить личный счёт на сумму	<input type="text" name="bs_summa" class="bs_input" id="bs_summa" size="3" value="{get_summ}" onkeyup="bs_topay()" /> {module_valuta}. 
		</div>

	<input type="hidden" name="bs_paysys" id="bs_paysys" />
	<input type="hidden" name="bs_hash" value="{hash}" />
	
	<div class="bt_billings">

		<div class="bt_title">Выберите способ оплаты:</div>
		
		[paysys]
			<div class="bt_billing" id="{paysys_name}" onClick="bs_paysys('{paysys_name}', '{paysys_title}', '{paysys_convert}', '{paysys_valuta}', '{paysys_minimum}', '{paysys_maximum}')">
			  <p><img src="{paysys_icon}" class="bt_billing-icon"> <span class="bt_billing-unit">{paysys_title}</span></p>
			</div>
		[/paysys]
	  
	</div>

	<div class="bt_billing-features"></div>

	<p style="padding: 10px; text-align: center">
		<button type="submit" id="bs_submit" name="submit" class="bs_button" style="opacity: 0.6">Оплатить <span id="bs_pay">{get_summ}</span> <span id="bs_pay_currency">{module_valuta}</span></button>
	</p>

</form>
	