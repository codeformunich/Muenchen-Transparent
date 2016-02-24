<?php
/**
 * @var StadtraetIn[][] $fraktionen
 * @var string $title
 */
?>
<section class="well"><?
    $insgesamt = 0;
    foreach ($fraktionen as $fraktion)
        $insgesamt += count($fraktion);
    ?>

    <h2><?= Html::encode($title) ?> <span style="float: right"><?= $insgesamt ?></span></h2>

    <ul class="fraktionen_liste"><?
        usort($fraktionen, function ($val1, $val2) {
            if (count($val1) < count($val2)) return 1;
            if (count($val1) > count($val2)) return -1;
            return 0;
        });
        foreach ($fraktionen as $fraktion) {
            /** @var StadtraetIn[] $fraktion */
            $fr = $fraktion[0]->stadtraetInnenFraktionen[0]->fraktion;
            echo "<li><a href='" . Html::encode($fr->getLink()) . "' class='name'><span class=\"glyphicon glyphicon-chevron-right\"></span>";
            echo "<span class='count'>" . count($fraktion) . "</span>";
            echo Html::encode($fr->getName()) . "</a><ul class='mitglieder'>";
            $mitglieder = StadtraetIn::sortByName($fraktion);
            foreach ($mitglieder as $mitglied) {
                echo "<li>";
                echo "<a href='" . Html::encode($mitglied->getLink()) . "' class='ris_link'>" . Html::encode($mitglied->getName()) . "</a>";
                if ($mitglied->abgeordnetenwatch != "") echo "<a href='" . Html::encode($mitglied->abgeordnetenwatch) . "' title='Abgeordnetenwatch' class='abgeordnetenwatch_link'></a>";
                if ($mitglied->web != "") echo "<a href='" . Html::encode($mitglied->web) . "' title='Homepage' class='web_link'></a>";
                if ($mitglied->twitter != "") echo "<a href='https://twitter.com/" . Html::encode($mitglied->twitter) . "' title='Twitter'           class='twitter_link'>T         </a>";
                if ($mitglied->facebook != "") echo "<a href='https://www.facebook.com/" . Html::encode($mitglied->facebook) . "' title='Facebook'          class='fb_link'>     f         </a>";
                echo "</li>\n";
            }
            echo "</ul></li>\n";
        }
        ?></ul>

    <script>
        $(function () {
            var $frakts = $(".fraktionen_liste > li");
            $frakts.addClass("closed").find("> a").click(function (ev) {
                if (ev.which == 2 || ev.which == 3) return;
                ev.preventDefault();
                var $li = $(this).parents("li").first();
                if ($li.hasClass("closed")) {
                    $li.removeClass("closed");
                    $li.find(".glyphicon").removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down");
                } else {
                    $li.addClass("closed");
                    $li.find(".glyphicon").removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-right");
                }
            });
        })
    </script>
</section>