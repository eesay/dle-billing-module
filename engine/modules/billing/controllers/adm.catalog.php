<?php	if( !defined( 'BILLING_MODULE' ) ) die( "Hacking attempt!" );
/*
=====================================================
 Billing
-----------------------------------------------------
 evgeny.tc@gmail.com
-----------------------------------------------------
 This code is copyrighted
=====================================================
*/

Class ADMIN 
{
	function main() 
	{
		/* CURL */
		$GetArray = array();
	
		if( !$this->DevTools->config['url_catalog'] )
			$this->DevTools->ThemeMsg( $this->DevTools->lang['catalog_er'], $this->DevTools->lang['catalog_er_title'], $PHP_SELF . "?mod=billing" );
	
		#$GetArray = json_decode( $this->DevTools->GetCache('billingCatalog'), true);
	
		if( !$GetArray )
		{
			if( $curl = curl_init() )
			{
				curl_setopt($curl, CURLOPT_URL, $this->DevTools->config['url_catalog']);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				
				$Get = curl_exec($curl);
				$GetArray = json_decode(iconv("UTF-8", $this->DevTools->config_dle['charset'], $Get ), true);
				
				$this->DevTools->CreatCache('billingCatalog', iconv("UTF-8", $this->DevTools->config_dle['charset'], $Get ) );
				
				curl_close($curl);
			
			} else
				$this->DevTools->ThemeMsg( $this->DevTools->lang['catalog_er'], $this->DevTools->lang['catalog_er2_title'], $PHP_SELF . "?mod=billing" );
	
		}
	
		if( !$GetArray )
			$this->DevTools->ThemeMsg( $this->DevTools->lang['catalog_er'], $this->DevTools->lang['catalog_er_title'], $PHP_SELF . "?mod=billing" );
	
		/* PAGE */
		$Content = $this->DevTools->ThemeEchoHeader() . $this->DevTools->MakeMsgInfo( (( $GetArray['version'] == $this->DevTools->config['version'] ) ? $this->DevTools->lang['catalog_version_yes'] : $this->DevTools->lang['catalog_version_no'])." {$GetArray['version']}", "icon-info-sign", "green" );
		
		/* Pars */
		$GetPaysys = "";
		$GetPlugins = "";
		
		$PaysysArray = $this->DevTools->GetPaysysArray();
		$PluginsArray = $this->DevTools->GetPluginsArray();
		
		foreach( $GetArray['plugins'] as $GetAPid => $GetAP )
		{
			// - Paysys
			if( $GetAP['cat'] == "3" )
				$GetPaysys .= $this->DevTools->ThemeCatalogItem( $GetAP, $this->DevTools->PaysysVersion( $GetAPid ) );
		
			// - Plugins
			if( $GetAP['cat'] == "2" )
				$GetPlugins .= $this->DevTools->ThemeCatalogItem( $GetAP, $PluginsArray[mb_strtolower($GetAPid)]['version'] );
		}
		
$Content .= <<<HTML
<div class="box">
		    <div class="box-header">
				<ul class="nav nav-tabs nav-tabs-left">
					<li class="active"><a href="#Paysys" data-toggle="tab">{$this->DevTools->lang['catalog_tab1']}</a></li>
					<li><a href="#Plugins" data-toggle="tab">{$this->DevTools->lang['catalog_tab2']}</a></li>
				</ul>
			</div>
		
            <div class="box-content">
                 <div class="tab-content">
					<div class="tab-pane active" id="Paysys">
						<div class="dd">
							{$GetPaysys}
						</div>
						{$this->DevTools->ThemePadded()}
					</div>
					<div class="tab-pane" id="Plugins">
						<form action="" method="post">
							{$GetPlugins}
							{$this->DevTools->ThemePadded()}
						</form>
					</div>
				</div>
			</div>
</div>
HTML;
		
		$Content .= $this->DevTools->ThemeEchoFoother();

		return $Content;
	}

}
?>