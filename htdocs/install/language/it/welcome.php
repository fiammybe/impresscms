<?php
// $Id: welcome.php 12227 2013-07-19 08:07:21Z fiammy $
$content .= '
<p>
	<unk> ImpressCMS è un sistema di pubblicazione Web scritto in PHP. È uno strumento ideale per
	<unk> sviluppando siti web di comunità dinamici di piccole o grandi dimensioni, portali aziendali, weblogs e molto altro ancora.
</p>
<p>
	<unk> ImpressCMS è rilasciato secondo i termini della
	<a href="http://www.gnu.org/copyleft/gpl.html" rel="external">GNU General Public License (GPL)</a>
	<unk> ed è libero di usare e modificare.
	<unk> È libero di ridistribuire fino a quando si rispettano i termini di distribuzione della GPL.
</p>
<h3>Requisiti</h3>
<ul>
	<li>- WWW Server: <a href="http://www.apache.org/" rel="external">Apache</a>NGinx, IIS, Roxen, etc</li>
	<li>- Script: <a href="http://www.php.net/" rel="external">PHP</a> 7.4+ e allocazione di memoria minima 32mb - PHP 8.3 è altamente raccomandato!</li>
	<li>- Database: <a href="http://www.mysql.com/" rel="external">MySQL</a> 4.1 o superiore, <a href="https://mariadb.org/" rel="external">MariaDB</a> 5.2 o superiore</li>
</ul>
<h3>Prima di installare</h3>
<ul>
	<li>Impostare il server web, PHP e server database correttamente.</li>
	<li>Prepara un database per ImpressCMS. Può trattarsi di un database esistente e di un nuovo database.</li>
	<li>Preparare un account utente e concedere a questo utente l\'accesso al database (tutti i diritti).</li>
	<li>Rendi le directory di uploads/, cache/, templates_c/, modules/ writable (chmod 777 o 755 - a seconda dei server).</li>
	<li>Crea il file principale. hp scrivibile (chmod 666 a seconda del server).</li>
	<li>Nelle impostazioni del browser internet attivare la tolleranza di cookie e JavaScript.</li>
</ul>
';