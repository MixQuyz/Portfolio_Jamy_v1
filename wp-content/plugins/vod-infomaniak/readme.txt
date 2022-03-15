=== Plugin Name ===

Contributors: Infomaniak Network
Plugin Name: Vod Infomaniak
Tags: video, manage, infomaniak, vod
Requires at least: 2.8.6
Tested up to: 4.3.1
Stable tag: 1.4.4



Easily embed and manage videos from Infomaniak VOD in your posts, comments and RSS feeds. You need an Infomaniak VOD account to use this plugin.
This plugin communicates directly with infomaniak.com 's services, notably by API, or for uploading videos to their servers.

== Description ==

Ce plugin vous permet de gérer facilement les interactions entre votre blog wordpress et votre espace VOD.
Il vous permet en toute simplicité de récupérer et de gérer l'ensemble de vos vidéos.

Si vous souhaitez obtenir plus d'informations sur notre solution d'hébergement vidéo, veuillez vous rendre à l'adresse http://streaming.infomaniak.com/stockage-video-en-ligne

Cet outil va vous permettre d'utiliser simplement depuis votre blog, de nombreuses fonctions avancées telles que :

* L'envoi de nouvelles vidéos pendant l'écriture d'un article
* La possibilité d'importer massivement des fichiers vidéos directement depuis votre administration wordpress
* La mise à jour automatique de la base de données de votre site lors de l'ajout d'une nouvelle vidéo à votre compte
* La récupération automatique des players existants
* La gestion et l'implémentation facile des playlist déjà créés dans notre interface d'administration
* Un outil de recherche de vidéo, lors de l'écriture d'un article ou d'une page pour retrouver facilement une archive

== Installation ==

Il est nécessaire pour utiliser ce plugin d'avoir un compte VOD sur notre interface d'administration http://statslive.infomaniak.ch/

Si vous souhaitez obtenir plus d'informations sur notre solution d'hébergement vidéo, veuillez vous rendre à l'adresse http://streaming.infomaniak.com/stockage-video-en-ligne

Pour installer ce plugin, il vous faut :

1. Charger le plugin `vod-infomaniak` dans le dossier `/wp-content/plugins/` de votre blog.
1. Aller activer ce plugin dans le menu plugins de wordpress.
1. Se rendre dans Gestion VOD -> Configuration afin de configurer votre compte avec les identifiants fournis sur l'interface d'administration.

Pour les mises à jour, celles-ci sont automatiquement proposées et se font sur le gestionnaire d'extension de wordpress.

== Frequently Asked Questions ==

= Il faut que je fournisse mes identifiants personnels au plugin ? =

Cela fonctionne, mais pour des raisons de sécurités, il est fortement déconseillé de le faire.
Il est nettement plus prudent dans votre interface d'administration VOD de créer un nouvel utilisateur et de ne lui attribuer que les droits "Gestion API".
En cas de problème, il sera bien plus aisé de supprimer l'utilisateur ou de changer son mot de passe que de compromettre tous ses services.

= J'ai créé un player, un dossier ou une playlist mais ils n'apparaissent pas encore sur mon blog =

Le plugin est prévu pour se synchroniser régulièrement avec votre compte afin de récupérer les dernières modifications automatiquement.
Il peut cependant arriver que vous n'ayez pas le temps d'attendre que cela se synchronise automatiquement.
Dans ce cas-là, il faut se rendre sur la page Gestion VOD > Configuration et appuyer sur le bouton "Synchronisation rapide".

= J'ai envoyé de nouvelles vidéos, mais elles n'apparaissent pas dans la liste des vidéos du blog =

Cela peut provenir d'un problème avec l'adresse de callback. C'est une adresse qu'utilise notre système d'encodage pour prévenir votre blog/site qu'une nouvelle vidéo est disponible.
Cette adresse doit donc être joignable de façon publique. Pour plus d'informations, se reporter à la page Gestion VOD > Configuration

= Je n'ai pas trouvé de réponse à ma question =

Sur notre site internet, nous possédons une Foire au Question (http://hosting.infomaniak.ch/support/faq/categories/toutes-157-1.html) qui répond à un grand nombre de questions et propose des guides/tutoriaux vidéos.
Si vous ne trouvez pas de solution à votre question, vous pouvez aussi nous contacter par email.

== Screenshots ==

1. Article créer à l'aide du plugin et dans lequel on peut retrouver une vidéo.
2. Screenshot montrant le menu d'administration permettant de gérer ses vidéos/players/playlist

== Changelog ==

= 1.4.4 (28/07/2020) =
* Correction de bugs

= 1.4.3 (28/07/2020) =
* Correction de bugs liés à l'utilisation du plugin sur un site https (upload de videos)

= 1.4.2 (11/09/2019) =
* Correction de bugs

= 1.4.1 (10/09/2019) =
* Correction de bugs

= 1.4.0 (10/09/2019) =
* Correction de bugs

= 1.3.13 (26/08/2019) =
* update version

= 1.3.12 (09/01/2017) =
* La librairy Mcrypt étant déclarée comme DEPRECATED depuis PHP 7.1, utilisation de OpenSSL si cette dernière est supportée.
* Cette mise à jour impose une déconnexion/reconnexion. La déconnexion est forcée à la mise à jour, l'utilisateur devra ressaisir son mot de passe de connection à l'API infomaniak.

= 1.3.11 (15/09/2016) =
* Modification pour meilleurs prise en compte du responsive (mise a jour 2)

= 1.3.10 (15/09/2016) =
* Modification pour meilleurs prise en compte du responsive (mise a jour)

= 1.3.9 (15/09/2016) =
* Modification pour meilleurs prise en compte du responsive

= 1.3.8 (18/05/2016) =
* Fix problème compatibilité PHP7 (suite)

= 1.3.7 (18/05/2016) =
* Fix problème compatibilité PHP7

= 1.3.6 (25/01/2016) =
* Prise en compte HTTPS si besoin

= 1.3.5 (25/01/2016) =
* Prise en compte HTTPS si besoin

= 1.3.4 (27/09/2015) =
* Force update

= 1.3.3 (27/09/2015) =
* Force update

= 1.3.2 (27/09/2015) =
* Fix option stretch qui ne fonctionnait pas correctement

= 1.3.1 (21/09/2015) =
* Force mise à jour version

= 1.2.9 (21/09/2015) =
* Force mise à jour version

= 1.2.8 (21/09/2015) =
* Fix problème de taille des players dans le cas ou WP est configuré en Anglais

= 1.2.7 (20/09/2015) =
* Mise à jour version

= 1.2.6 (20/09/2015) =
* Fix bug fullscreen sur le player video lors du fallback html5

= 1.2.5 (03/08/2015) =
* Fix bug importation video depuis un dossier different

= 1.2.4 (03/08/2015) =
* Fix version

= 1.2.3 (03/08/2015) =
* Fix accent sur le module de langues
* Ajout tuto video dans la page de config du plugin

= 1.2.2 (14/04/2015) =
* Suppresion des données du plugin lors de sa desinstallation
* Fix bug quand on ajoute une vidéo avec lien direct
* Fix problèmes d'affichage de paramètres avec certains caractères spéciaux
* Mise a jours des traductions

= 1.2.1 (08/04/2015) =
* Fix bug vidéo avec token

= 1.2 (08/01/2015) =
* Vérification de la compatiblité avec Wordpress 4.1
* Fix du bug d'interprétation de la taille du player lors de l'ajout d'une vidéo dans un article.
* Adaptation PHP 5.6

= 1.1.9 (19/06/2014) =
* Ajout d'un avertissement lors de l'insertion depuis un article si le plugin n'est pas configuré.

= 1.1.8 (17/06/2014) =
* Ajout de la création d'un article depuis la liste des playlistes

= 1.1.7 (22/04/2014) =
* Récupération des options du player depuis l'interface d'administration
* Ajout d'un nouvel onglet « Playlists »
* Suppression de l’onglet « Outils de recherche »
* Intégration de la recherche directement dans les onglets « Vidéos » et « Playlists »
* Divers petites corrections

= 1.1.6 (15/01/2014) =
* Renommage d'un des fichiers qui pouvait poser quelques problèmes à certains gestionnaires FTP

= 1.1.5 (14/01/2014) =
* Correction de plusieurs notices suites aux updates de wordpress (3.7 et 3.8)
* Correction du styles de plusieurs boutons
* Reformatage du code

= 1.1.2 (07/10/2013) =
* Correction d'un problème d'affichage lié au système de recherche

= 1.1.1 (03/10/2013) =
* Correction lié aux dernières versions de jquery intégré à wordpress 3.6.x (dialog lors de la redaction d'un article)
* Correction lié aux htmlspecialchar dans le listing des vidéos

= 1.1.0 (06/08/2012) =
* Ajout d'une fonctionnalité permettant de n'afficher qu'une partie des dossiers/videos disponible sur l'espace VOD
* Ajout d'une fonctionnalité permettant de gerer les droits d'accès aux différentes partie du plugin
* Correction des URL pointant vers l'interface d'administration
* Modification de l'outil d'autoconfiguration du callback afin d'utiliser la nouvelle version automatiquement

= 1.0.1 (23/03/2012) =
* Correction d'un bug sur la page de selection du player
* Changement de l'adresse de la FAQ

= 1.0.0 (10/01/2012) - Release Final =
* Après 2 Release Candidate (0.9.0, 0.9.1) et 1 mois en RC2 considéré comme stable, passage en version final
* Modification du comportement des parametres autoplay et loop lors de l'integration.
* Ajout des traductions EN,DE
* Version de controle avant la version finale

= 0.2.6 (28/09/2011) =
* Suppression d'un warning pouvant apparaitre sur certaines configurations
* Ajout d'accents sur quelques phrases n'étant pas présentes dans les fichiers de traductions
* Remise en forme du formulaire d'ajout d'une vidéo
* Nouveau système d'update automatique des tables mysql
* Ajout de la possibilité d'uploader une vidéo directement lors de l'écriture d'un article/page via un onglet "Envoi d'une vidéo"
* Modification du loading de certaines fonctions par le plugin qui pouvait rentrer en conflit avec certains thèmes.
* Correction d'un problème sur la récupération des playlist

= 0.2.4 (14/09/2011) =
* Utilisation du mode de debug de wordpress au lieu d'erreur PHP en cas de problème avec l'API
* Modification du fonctionnement du renommage d'une vidéo

= 0.2.3 (05/09/2011) =
* Ajout d'un try/catch sur la récupération des importations, cela pouvant provoquer une erreur s'il n'y a aucune importation récente.
* Nouvelle option lors de l'écriture/édition d'un article permettant de choisir parmi les 50 dernières vidéos.
* Quelques petites optimisations
* Fix d'un bug avec le système de synchro lors d'une première installation et que rien n'a encore été configuré.

= 0.2 (01/09/2011) =
* Modification du nouveau système de synchro pour qu'il se lance plus régulièrement contrairement à wp_schedule_event()
* Suppression des notices pouvant être indiquées par apache
* Correction d'un bug d'affichage dans le cas de playlist incomplète
* Nouveau système de synchro automatique des vidéos plus efficace
* Fix d'un bug durant la synchro forcé des dossiers
* Récuperation et affichage de la durée des playlist
* Correction d'orthographe sur plusieurs phrases
* Snapshot de prévisualisation
* Fix d'un bug d'affichage en 1024
* Correction concernant l'écriture automatique d'un post lorsque la vidéo nécessite un token
* Correction du message indiquant des géolocalisations sur certaines vidéos n'en ayant pas
* Mise en place d'une première version du système de traduction
* Correction dans l'appel d'une fonction de wordpress deprecated
* Option permettant de créer automatiquement un article en draft à partir d'une vidéo
* Amélioration de la page player avec un player de prévisualisation pour pouvoir tester celui sélectionné
* Gestion automatique des token unique sur les vidéos le nécessitant. L'ajout d'un attribut 'tokenfolder' à la balise [vod] permet au plugin d'identifier les vidéos utilisant cette fonctionnalité et ainsi de générer à la volée la clef unique pour chaque visiteur.
* Focus automatique sur les champs des formulaires de recherches
* Ajout de nouveaux liens sur les vidéos (statistiques, détails d'une vidéo)
* Possibilité de renommer et supprimer une vidéo
* Nouveau système de cryptage du password de l'API

= 0.1 (23/08/2011) =
* Lancement du projet et première version publiée sur wordpress.org

= Prochainement =
* Gestion des fichiers audios
* Gestion des sous-titres
* URL personnalisable
