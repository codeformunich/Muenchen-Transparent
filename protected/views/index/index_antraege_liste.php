<?php

/**
 * @var IndexController $this
 * @var Antrag[] $antraege
 * @var string|null $weitere_url
 * @var string $datum
 * @var float $geo_lng
 * @var float $geo_lat
 * @var float $radius
 * @var OrtGeo $naechster_ort
 */

if (isset($datum)) {
	$erkl_str = RISTools::datumstring($datum);
	if ($erkl_str > 0) $erkl_str = "vom " . $erkl_str;
	else $erkl_str = "von " . $erkl_str;
} else {
	$erkl_str = ": etwa ${radius}m um \"" . CHtml::encode($naechster_ort->ort) . "\"";
}
?>
<h3>Stadtratsdokumente <?= $erkl_str ?></h3>
<ul class="antragsliste">
	<?
	foreach ($antraege as $ant) if (!method_exists($ant, "getName")) {
		echo "<li>" . get_class($ant) . "</li>";
	} else {
		echo "<li><div class='antraglink'>" . CHtml::link($ant->getName(), $ant->getLink()) . "</div>";

		$max_date = 0;
		$doklist  = "";
		foreach ($ant->dokumente as $dokument) {
			$doklist .= "<li>" . CHtml::link($dokument->name, $this->createUrl("index/dokument", array("id" => $dokument->id))) . "</li>";
			$dat = RISTools::date_iso2timestamp($dokument->datum);
			if ($dat > $max_date) $max_date = $dat;
		}

		echo "<div class='add_meta'>";
		$parteien = array();
		foreach ($ant->antraegePersonen as $person) {
			$name   = $person->person->name;
			$partei = $person->person->ratePartei($ant->gestellt_am);
			if (!$partei) {
				$parteien[$name] = array($name);
			} else {
				if (!isset($parteien[$partei])) $parteien[$partei] = array();
				$parteien[$partei][] = $person->person->name;
			}
		}

		$p_strs = array();
		foreach ($parteien as $partei => $personen) {
			$str = "<span class='partei' title='" . CHtml::encode(implode(", ", $personen)) . "'>";
			$str .= CHtml::encode($partei);
			$str .= "</span>";
			$p_strs[] = $str;
		}
		if (count($p_strs) > 0) echo implode(", ", $p_strs) . ", ";
		echo date("d.m.", $max_date);
		echo "</div>";

		echo "<ul class='dokumente'>";
		echo $doklist;
		echo "</ul></li>\n";
	}
	?>
</ul>
<? if ($weitere_url !== null) { ?>
<div class="aeltere_caller">
	<a href="#" onClick="return index_aeltere_dokumente_load('<?= CHtml::encode($weitere_url) ?>');"><span class="glyphicon glyphicon-chevron-right"></span> Ältere Dokmente</a>
</div>
<? } ?>