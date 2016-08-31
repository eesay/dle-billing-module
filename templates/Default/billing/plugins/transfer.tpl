<div class="bt_title">Перевод средств</div>

<div class="bt_panel">
	<form action="" name="Refundform" method="post">

			<p>Сумма перевода: <input type="text" name="bs_summa" class="bs_input" size="3" value="{get_summ}" /> {get_summ_valuta}, для <input type="text" name="bs_user_name" class="bs_input" size="15" value="{to}" placeholder="логин получателя" /> <button type="submit" name="submit" class="bs_button"><span>Далее</span></button></p>
			
		<input type="hidden" name="bs_hash" value="{hash}" />
		
	</form>
</div>

<p style="text-align: center">Минимум: {minimum} {minimum_valuta}, из них комиссия {commission}%</p>

<div class="bt_title" style="padding-top: 15px">История перевода средств</div>

	 <table class="bt_table-normal">
		<tr>
            <td><b>Дата</b></td>
            <td><b>Сумма</b></td>
            <td><b>Пользователь</b></td>
        </tr>
       [history]
		<tr>
            <td>{date=j.m.Y G:i}</td>
            <td>{transfer_summa}</td>
            <td>{transfer_user}</td>
        </tr>
    	[/history]
        [not_history]
        <tr>
            <td colspan="3">&raquo; Записей не найдено</td>
        </tr>
        [/not_history]

	 </table>

[paging]
	<div class="bt_pagination">
		[page_link]<a href="{page_num_link}">{page_num}</a>[/page_link]
		[page_this] <strong>{page_num}</strong> [/page_this]
	</div>
[/paging]