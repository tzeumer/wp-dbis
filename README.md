# wp-dbis
Browse DBIS (http://rzblx10.uni-regensburg.de/dbinfo/) within Wordpress

Weglinken von der eigenen Seite ist nicht immer schön, wenn man doch nur sein eigenes Angebot zeigen will. :)

## Schnelleinstieg
1. Das Plugin unter Wordpress installieren (als Adresse einfach angeben: https://github.com/tzeumer/wp-dbis/archive/master.zip)
2. Im Dashboard unter Einstellungen > DBIS-Einstellungen die DBIS-ID der eigenen Bibliothek angeben (das ist die bib_id in dem URL, wenn die eigene Bibliothek ausgewählt ist)
3. Seite erstellen (z.B. Datenbanken) und den Shortcode [dbis] einfügen

## Bekannte Limits
1. Je mehr Datenbanken in einer Kategorie sind, desto länger dauert das Laden. Üblicherweise immer noch fix genug, aber z.B. "Neue DBs (letzten 365 Tage)" kann das meist gesetzte Script-Timeout von 30 Sekunden in PHP sprengen.

## Todos
* Caching: Nicht jedes Mal alles live abrufen
* Ordentliche Templates (im Moment müssen die Links unter inc\dbis\templates\default\*.tpl angepasst werden)
* Ggf. mehr Options für das WP-Einstellungs-Menü

## Hinweis
Im Moment keine Tätigkeit in diesem "Projekt". Es ist nur hier gelandet, weil es eben auf der Platte lag. Und lag. Und lag... ;)

## Changelog
* 2022-05-05: Update dependency fabpot/goutte ~2 to ~4
* 2021-01-27: Kleiner Bugfix und wieder Top Datenbanken als Standard, aber mit alphabetischer Sortierung als Vorgabe
* 2021-01-26: "Alphabetische Liste" als Abfrage hinzugefügt und zum Standardeinstieg gemacht