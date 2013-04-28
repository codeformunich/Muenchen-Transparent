<?php

class Recalc_DocumentsCommand extends CConsoleCommand {
	public function run($args) {

		define("VERYFAST", true);

		$sql = Yii::app()->db->createCommand();
		$sql->select("id")->from("antraege_dokumente")->where("id < 1238052")->order("id");
		$data = $sql->queryColumn(array("id"));

		$anz = count($data);
		foreach ($data as $nr => $dok_id) {
			echo "$nr / $anz => $dok_id\n";
			/** @var AntragDokument $dokument */
			$dokument = AntragDokument::model()->findByPk($dok_id);

			$url      = "http://www.ris-muenchen.de" . $dokument->url;
			$x        = explode("/", $url);
			$filename = $x[count($x) - 1];
			$absolute_filename = PDF_PDF . $filename;
			$dokument->seiten_anzahl = RISPDF2Text::document_anzahl_seiten($absolute_filename);
			$dokument->save();

			echo $filename . " => " . $dokument->seiten_anzahl . "\n";
		}
	}
}