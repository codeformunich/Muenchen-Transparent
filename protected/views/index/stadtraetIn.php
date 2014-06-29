<?php
/**
 * @var StadtraetIn $stadtraetIn
 * @var IndexController $this
 */

$this->pageTitle = $stadtraetIn->getName();


?>
<h1><?= CHtml::encode($stadtraetIn->getName()) ?></h1>

<div class="row">
	<div class="col-md-8">
		<table class="table table-bordered">
			<tbody>
			<tr>
				<th>Fraktion(en):</th>
				<td>
					<div style="float: right;"><?
						echo CHtml::link("<span class='icon-right-open'></span> Original-Seite im RIS", $stadtraetIn->getSourceLink());
						?></div>
					<ul>
						<? foreach ($stadtraetIn->stadtraetInnenFraktionen as $frakts) {
							echo "<li>" . CHtml::encode($frakts->fraktion->name) . "</li>";
						} ?>
					</ul>
				</td>
			</tr>
				<tr>
					<th>Anträge:</th>
					<td>
						<ul>
							<?
							foreach ($stadtraetIn->antraege as $antrag) {
								echo "<li>";
								echo CHtml::link($antrag->betreff, $antrag->getLink());
								echo " (" . RISTools::datumstring($antrag->gestellt_am) . ")";
								echo "</li>\n";
							}
							?>
						</ul>
					</td>
				</tr>

			</tbody>
		</table>
	</div>
	<section style="background-color: #f7f7f7; padding-top: 10px; padding-bottom: 10px;" class="col-md-4">
	</section>
</div>