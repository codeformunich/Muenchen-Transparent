<?php
/**
 * @var Antrag $antrag
 * @var AntraegeController $this
 * @var bool $tag_mode
 */

$this->pageTitle         = $antrag->getName(true);
$this->load_selectize_js = true;
$this->load_shariff      = true;

$personen = [
    AntragPerson::$TYP_GESTELLT_VON => [],
    AntragPerson::$TYP_INITIATORIN  => [],
];
foreach ($antrag->antraegePersonen as $ap) $personen[$ap->typ][] = $ap->person;

$historie = $antrag->getHistoryDiffs();

$name = $antrag->getName(true);

function zeile_anzeigen($feld, $name, $css_id, $callback) {
    if (count($feld) == 0) 
        return;
    ?>
    
    <tr <? if ($css_id != "") echo 'id="' . $css_id . '"'; ?>>
        <th><? echo $name ?></th>
        <td>
            <? if (count($feld) == 1) {
                $callback($feld[0]);
            } else { ?>
                <ul>
                    <? foreach ($feld as $element) { ?>
                        <li>
                            <? $callback($element); ?>
                        </li>
                    <? } ?>
                </ul>
            <? } ?>
        </td>
    </tr>
<? }

function verbundene_anzeigen($antraege, $ueberschrift, $css_id, $this2) {
    zeile_anzeigen($antraege, $ueberschrift, $css_id, function ($element) use (&$this2){
        /** @var Antrag $element */
        echo Html::link($element->getName(true), $this2->createUrl("antraege/anzeigen", array("id" => $element->id)));
        echo '<div class="metainformationen_verbundene">';
        if ($element->antrag_typ != "" && $element->antrag_typ != "Antrag")
            echo "<span>" . Html::encode($element->antrag_typ) . "</span>";
        $this2->renderPartial("/antraege/metainformationen", array(
            "antrag" => $element,
            "zeige_ba_orte" => true
        ));
        echo "</div>";
    });
}

?>

<section class="row">
    <div class="col-md-8">
        <section class="well">
            <div class="original_ris_link"><?
                echo Html::link("<span class='fontello-right-open'></span>Original-Seite im RIS", $antrag->getSourceLink());
                ?></div>
            <h1 class="small"><? echo "<strong>" . Yii::t('t', Antrag::$TYPEN_ALLE[$antrag->typ], 1) . "</strong>";
                if ($antrag->antrag_typ != "") echo " (" . Html::encode($antrag->antrag_typ) . ")"; ?></h1>

            <p style="font-size: 18px;"><?= Html::encode($name) ?></p>

            <table class="table antragsdaten">
                <tbody>
                <tr id="schlagworte">
                    <th><?
                        if ($this->aktuelleBenutzerIn()) echo '<label for="antrag_tags">Schlagworte:</label>';
                        else echo 'Schlagworte:';
                        ?></th>
                    <td><?
                        if (count($antrag->tags) == 0) echo '<em>noch keine</em>';
                        else {
                            echo '<ul class="antrags_tags">';
                            foreach ($antrag->tags as $tag) echo '<li>' . $tag->getNameLink() . '</li>';
                            echo '</ul>';
                        }
                        if ($this->aktuelleBenutzerIn()) {
                            ?>
                            &nbsp; &nbsp;
                            <a href="#tag_add_form" class="tag_add_opener"><span
                                    class="glyphicon glyphicon-chevron-down"></span> Neue hinzufügen</a>
                            <form method="post" id="tag_add_form" style="display: none;">
                                <input name="tags_neu" type="text" id="antrag_tags" value="">
                                <button class="btn btn-primary" type="submit"
                                        name="<?= AntiXSS::createToken("tag_add") ?>">Speichern
                                </button>
                            </form>
                        <?
                        } else {
                            ?>
                            <form method="POST" action="<?= Html::encode($antrag->getLink(["tag_mode" => 1])) ?>"
                                  class="login_modal_form">
                                <?
                                $this->renderPartial("../index/login_modal");
                                ?>
                                &nbsp; &nbsp;
                                <a href="#tag_add_form" data-toggle="modal" data-target="#benachrichtigung_login"><span
                                        class="glyphicon glyphicon-chevron-down"></span> Neue
                                    hinzufügen (Login)</a>
                            </form>
                        <?
                        }
                        ?>

                        <script>
                            $(function () {
                                $('#antrag_tags').selectize({
                                    delimiter: ',',
                                    persist: false,
                                    wrapperClass: 'selectizejs-control',
                                    inputClass: 'selectizejs-input',
                                    dropdownClass: 'selectizejs-dropdown',
                                    dropdownContentClass: 'selectizejs-dropdown-content',
                                    create: true,
                                    render: {
                                        "option_create": function (data, escape) {
                                            return '<div class="create">Neues Schlagwort: <strong>' + escape(data.input) + '</strong>&hellip;</div>';
                                        }
                                    },

                                    load: function (query, callback) {
                                        if (!query.length) return callback();
                                        $.ajax({
                                            url: '<?=CHtml::encode(Yii::app()->createUrl("antraege/ajaxTagsSuggest"))?>/?term=' + encodeURIComponent(query),
                                            type: 'GET',
                                            error: function () {
                                                callback();
                                            },
                                            success: function (res) {
                                                callback(res);
                                            }
                                        });
                                    }
                                });
                                $(".tag_add_opener").click(function (ev) {
                                    ev.preventDefault();
                                    $('#tag_add_form').show().find('.selectizejs-input input').focus();
                                    $(this).hide();
                                    return false;
                                });
                                <?
                                if ($tag_mode) echo '$(".tag_add_opener").click();';
                                ?>
                            });
                        </script>
                    </td>
                </tr>
                <?
                zeile_anzeigen($personen[AntragPerson::$TYP_INITIATORIN], "Initiiert von:", "initiatoren",  function ($person) use ($antrag) {
                    /** @var Person $person */
                    if ($person->stadtraetIn) {
                        echo Html::link($person->stadtraetIn->name, $person->stadtraetIn->getLink());
                        echo " (" . Html::encode($person->ratePartei($antrag->gestellt_am)) . ")";
                    } else {
                        echo Html::encode($person->name);
                    }
                });
                zeile_anzeigen($personen[AntragPerson::$TYP_GESTELLT_VON], "Gestellt von:", "gestellt_von",  function ($person) use ($antrag) {
                    /** @var Person $person */
                    if ($person->stadtraetIn) {
                        echo Html::link($person->stadtraetIn->name, $person->stadtraetIn->getLink());
                        echo " (" . Html::encode($person->ratePartei($antrag->gestellt_am)) . ")";
                    } else {
                        echo Html::encode($person->name);
                    }
                });
                ?>
                <tr id="gremium">
                    <th>Gremium:</th>
                    <td><?
                        if ($antrag->ba_nr > 0) {
                            echo Html::link("Bezirksausschuss " . $antrag->ba_nr, $antrag->ba->getLink()) . " (" . Html::encode($antrag->ba->name) . ")";
                        } else {
                            echo "Stadtrat";
                        }
                        if ($antrag->referat != "") echo " / " . Html::encode(strip_tags($antrag->referat));
                        ?></td>
                </tr>
                <tr id="antragsnummer">
                    <th>Antragsnummer:</th>
                    <td><?= Html::encode($antrag->antrags_nr) ?></td>
                </tr>
                <?
                if ($antrag->gestellt_am > 0 && $antrag->gestellt_am == $antrag->registriert_am) {
                    echo "<tr><th>Gestellt u. registriert: </th><td>" . Html::encode(RISTools::datumstring($antrag->gestellt_am)) . "</td></tr>\n";
                } else {
                    if ($antrag->gestellt_am > 0)    echo "<tr><th>Gestellt am:</th><td>"       . Html::encode(RISTools::datumstring($antrag->gestellt_am))        . "</td></tr>\n";
                    if ($antrag->registriert_am > 0) echo "<tr><th>Registriert am:</th><td>"    . Html::encode(RISTools::datumstring($antrag->registriert_am))     . "</td></tr>\n";
                }
                if ($antrag->bearbeitungsfrist > 0)  echo "<tr><th>Bearbeitungsfrist:</th><td>" . Html::encode(RISTools::datumstring($antrag->bearbeitungsfrist))  . "</td></tr>\n";
                if ($antrag->fristverlaengerung > 0) echo "<tr><th>Fristverlängerung:</th><td>" . Html::encode(RISTools::datumstring($antrag->fristverlaengerung)) . "</td></tr>\n";
                if ($antrag->erledigt_am > 0)        echo "<tr><th>Erledigt am:</th><td>"       . Html::encode(RISTools::datumstring($antrag->erledigt_am))        . "</td></tr>\n";
                ?>
                <tr id="status">
                    <th>Status:</th>
                    <td><?
                        echo Html::encode($antrag->status);
                        if ($antrag->bearbeitung != "") echo " / ";
                        echo Html::encode($antrag->bearbeitung);
                        ?></td>
                </tr>
                <tr id="wahlperiode">
                    <th>Wahlperiode:</th>
                    <td><?= Html::encode($antrag->wahlperiode) ?></td>
                </tr>
                <?
                $docs = $antrag->dokumente;
                usort($docs, function ($dok1, $dok2) {
                    /**
                     * @var Dokument $dok1
                     * @var Dokument $dok2
                     */
                    $ts1 = RISTools::date_iso2timestamp($dok1->getDate());
                    $ts2 = RISTools::date_iso2timestamp($dok2->getDate());
                    if ($ts1 > $ts2) return 1;
                    if ($ts1 < $ts2) return -1;
                    return 0;
                });
                zeile_anzeigen($docs, "Dokumente:", "dokumente",  function ($dokument) {
                    /** @var Dokument $dok */
                    echo Html::encode($dokument->getDisplayDate()) . ": " . Html::link($dokument->getName(false), $dokument->getLinkZumDokument());
                    ?> <a class="fontello-download antrag-herunterladen" href="<?= Html::encode('/dokumente/' . $dokument->id . '.pdf') ?>" download="<?= $dokument->antrag_id ?> - <?= Html::encode($dokument->getName())?>.pdf" title="Herunterladen: <?= Html::encode($dokument->getName()) ?>"></a> <?
                });
                $angezeigte_dokumente = [];
                foreach ($docs as $d) $angezeigte_dokumente[] = $d->id;

                zeile_anzeigen($antrag->ergebnisse, "Behandelt:", "behandelt",  function ($ergebnis) {
                    /** @var Tagesordnungspunkt $termin */
                    $termin = $ergebnis->sitzungstermin;
                    echo Html::link(RISTools::datumstring($termin->termin) . ', ' . $termin->gremium->getName(), $termin->getLink());
                    if ($ergebnis->beschluss_text != '' || $ergebnis->entscheidung != '') {
                        echo '<br>';
                        $text = $ergebnis->beschluss_text;
                        if ($ergebnis->beschluss_text != '' && $ergebnis->entscheidung != '') $text .= ', ';
                        $text .= $ergebnis->entscheidung;
                        if (count($ergebnis->dokumente) == 1) {
                            echo "Ergebnis: " . Html::link($text, $ergebnis->dokumente[0]->getLink());
                        } else {
                            echo "Ergebnis: " . Html::encode($text);
                            foreach ($ergebnis->dokumente as $dok) {
                                echo '<br>' . Html::link($dok->getName(), $dok->getLink());
                            }
                        }
                    }
                });

                verbundene_anzeigen($antrag->antrag2vorlagen,  "Verbundene Stadtratsvorlagen:", "verbundene_stadtratsvorlagen", $this);
                verbundene_anzeigen($antrag->vorlage2antraege, "Verbundene Stadtratsanträge:" , "verbundene_stadtratsantraege", $this);

                if (count($historie) > 0) {
                    ?>
                    <tr id="historie">
                        <th>Historie: <span class="icon - info - circled" title="Seit dem 1. April 2014"
                                            style="font - size: 12px; color: gray;"></span></th> <? /* FIXME */ ?>
                        <td>
                            <ol>
                                <? foreach ($historie as $hist) {
                                    echo " <li>" . $hist->getDatum() . ": <ul> ";
                                    $diff = $hist->getFormattedDiff();
                                    foreach ($diff as $d) {
                                        echo "<li><strong> " . $d->getFeld() . ":</strong> ";
                                        echo "<del> " . $d->getAlt() . "</del> => <ins> " . $d->getNeu() . "</ins></li> ";
                                    }
                                    echo "</ul></li>\n";
                                } ?>
                            </ol>
                        </td>
                    </tr>
                <?
                }

                /** @var IRISItem[] $vorgang_items */
                if ($antrag->vorgang) {
                    $items = [];
                    foreach ($antrag->vorgang->getRISItemsByDate() as $item) {
                        // Der gerade angezeigte Antrag und seine Dokumente überspringen, sodass sie nicht als verwandte Seiten angezeigt werden
                        if (is_a($item, "Antrag") && $item->id == $antrag->id) continue;
                        if (is_a($item, "Dokument")) continue;
                        
                        $items[] = $item;
                    }
                    
                    zeile_anzeigen($items, "Verwandte Seiten:", "verwandte_seiten", function ($item) {
                        /** @var IRISItem $item */
                        if (method_exists($item, "getLinkZumDokument"))
                            echo Html::link($item->getName(true), $item->getLinkZumDokument());
                        else
                            echo Html::link($item->getName(true), $item->getLink());
                    });
                }
                ?>

                </tbody>
            </table>
        </section>
    </div>
    <section class="col-md-4 antrag_sidebar">

        <? $related = $antrag->errateThemenverwandteAntraege(7); ?>
        <div class="well themenverwandt_liste" id="themenverwandt">

            <form method="POST" action="<?= Yii::app()->createUrl("antraege/anzeigen", ["id" => $antrag->id]) ?>"
                  class="abo_button row_head" style="min-height: 80px; text-align: center;">
                <? if ($antrag->vorgang && $antrag->vorgang->istAbonniert($this->aktuelleBenutzerIn())) { ?>
                    <button type="submit" name="<?= AntiXSS::createToken("deabonnieren") ?>"
                            class="btn btn-success btn-raised btn-lg">
                        <span class="glyphicon">@</span> Abonniert
                    </button>
                <? } else { ?>
                    <button type="submit" name="<?= AntiXSS::createToken("abonnieren") ?>"
                            class="btn btn-info btn-raised btn-lg">
                        <span class="glyphicon">@</span> Nicht abonniert
                    </button>
                <? } ?>
            </form>

            <div class="shariff" data-backend-url="<?= Html::encode($this->createUrl("/index/shariffData")) ?>"
                 data-url="<?= Html::encode(Yii::app()->getBaseUrl(true) . $antrag->getLink()) ?>" data-services="[&quot;twitter&quot;, &quot;facebook&quot;]"></div>
        </div>
        <div class="well themenverwandt_liste">
            <?
            if (count($related)) {
                ?>
                <h2>Könnte themenverwandt sein</h2>


                <ul class="list-group">
                    <?
                    $this->renderPartial("related_list", [
                        "related" => $related,
                        "narrow"  => true,
                    ]);
                    ?>
                </ul>

                <a href="<?= Html::encode(Yii::app()->createUrl("antraege/themenverwandte", ["id" => $antrag->id])) ?>"
                   class="weitere">
                    Weitere Themenverwandte <span class="glyphicon glyphicon-chevron-right"></span>
                </a>
            <? } ?>
        </div>
    </section>
</section>