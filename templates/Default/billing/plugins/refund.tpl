<script type="text/javascript">
$(document).ready(function() {
	mask = '{mask}';
	if( mask )
	{
		$("#bs_requisites").inputmask("{mask}");
    }
});
</script>

<div class="bt_title">Создать запрос</div>

<div class="bt_panel">
	<form action="" name="Refundform" method="post">

			<p>Сумма вывода: <input type="text" name="bs_summa" class="bs_input" size="3" value="{minimum}" /> {minimum_valuta}, реквизиты: <input type="text" id="bs_requisites" name="bs_requisites" class="bs_input" size="15" value="{requisites}" /> <button type="submit" name="submit" class="bs_button"><span>Далее</span></button></p>
			
		<input type="hidden" name="bs_hash" value="{hash}" />
		
	</form>
</div>

<p style="text-align: center">Минимум: {minimum} {minimum_valuta}, из них комиссия {commission} %</p>

<div class="bt_title" style="padding-top: 15px">История вывода средств</div>

	 <table class="bt_table-normal">
		<tr>
            <td><b>Дата</b></td>
            <td><b>Сумма</b></td>
            <td><b>Из них комисия</b></td>
            <td><b>Реквизиты</b></td>
            <td><b>Выполнено</b></td>
        </tr>
       [history]
		<tr>
            <td>{date=j.m.Y G:i}</td>
            <td>{refund_summa} {refund_summa_valuta}</td>
            <td>{refund_commission} {refund_commission_valuta}</td>
            <td>{refund_requisites}</td>
            <td>{refund_status}</td>
        </tr>
    	[/history]
        [not_history]
        <tr>
            <td colspan="5">&raquo; Записей не найдено</td>
        </tr>
        [/not_history]

	 </table>

[paging]
	<div class="bt_pagination">
		[page_link]<a href="{page_num_link}">{page_num}</a>[/page_link]
		[page_this] <strong>{page_num}</strong> [/page_this]
	</div>
[/paging]