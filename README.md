# City of Helsinki - KASKO Drupal 9 project

## Environments

Env | Branch | Drush alias | URL
--- | ------ | ----------- | ---
development | * | - | https://helfi-kasko.docker.so/
production | main | @main | TBD

## Requirements

You need to have these applications installed to operate on all environments:

- [Docker](https://github.com/druidfi/guidelines/blob/master/docs/docker.md)
- [Stonehenge](https://github.com/druidfi/stonehenge)
- For the new person: Your SSH public key needs to be added to servers

## Create and start the environment

For the first time (new project):

``
$ make new
``

And following times to start the environment:

``
$ make up
``

NOTE: Change these according of the state of your project.

## Login to Drupal container

This will log you inside the app container:

```
$ make shell
```

## Instance specific features

The KASKO instance has multiple React searches and features provided by the Group module that other instances don’t
have.

### Custom paragraphs

#### <a name="after-school-search"></a>After-school activity search (after_school_activity_search)

This search paragraph lists TPR units that are tagged with the `unit_type` vocabulary term _After-school activity_.

- The filter search is a _view_ (`after_school_activity_search`) with exposed filters. The view configuration can be
found in [here](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/blob/dev/conf/cmi/views.view.after_school_activity_search.yml).
- No React front.
- The paragraph has editable title and description fields
- Can be added to landing pages and the lower content region of standard pages.

#### <a name="daycare-search"></a>Daycare search (daycare_search)

This search paragraph lists TPR units that are tagged with the `unit_type` vocabulary term _Daycare_.

- The filter search is a _view_ (`daycare_search`) with exposed filters. The view configuration can be found in [here](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/blob/dev/conf/cmi/views.view.daycare_search.yml).
- No React front.
- The paragraph has editable title and description fields
- Can be added to landing pages and the lower content region of standard pages.

#### <a name="group-news"></a>Group news (group_news)

The _Group news_ paragraph lists latest news of a selected group. The block uses a view called `latest_group_news` that
has two displays for different amount of news listed.

The number of news items to be displayed can be selected from the paragraph field `field_group_news_number_of_news`.
This selection determines the view display to be used. The related logic can be found in the `hdbt_subtheme` under the
group news paragraph template [here](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/blob/dev/public/themes/custom/hdbt_subtheme/templates/paragraph/paragraph--group-news.html.twig).

Additionally, the paragraph has a field called `field_group_news_group_id` that is used to identify the group whose
news should be displayed, and a field called `field_group_news_archive` where you can define the landing page to which
the paragraph should link. The paragraph also includes editable fields for the title and description.

You can add the paragraph to landing pages and the higher and lower content regions of standard pages.

#### <a name="group-news-archive"></a>Group news archive (group_news_archive)

The _Group news archive_ is simple paged list of all news the selected group has. The paragraph includes only one field
called `field_group_news_group_id` that is used to identify the group whose news should be displayed. The list is
a view called `group_news_archive` and its configuration can be found [here](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/blob/dev/conf/cmi/views.view.group_news_archive.yml).
You can add the paragraph to landing pages.

#### <a name="high-school-search"></a>High school search (high_school_search)

The _High school search_ lists high schools, also referred to as upper secondary schools in this instance. This search
was one of the first implementations of the unit searches, and it has a slightly different structure compared to the
[After-school activity search](#after-school-search), [Daycare search](#daycare-search), and [Playground search](#playground-search).

The units are selected manually in the paragraph field `field_hs_search_units`, and the view filters the TPR units to
display based on the field's content. It is also possible to change the form's submit button text by writing the desired
text in the `field_hs_search_meta_button` field. The results are displayed in two tabs, allowing users to either list
schools or display them on a map.

The exposed form has additional functionality provided by `high-school-search.js`, which makes it possible to select a
value only for the _Emphasis_ or _Mission_ drop-down. This helps avoid searches that yield no results.

- The filter search is a _view_ (`high_school_search`) with exposed filters. The view configuration can be found in
[here](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/blob/dev/conf/cmi/views.view.high_school_search.yml).
- The results can be displayed as a list or on a map.
- No React front.
- The paragraph has editable title, description, units and search button fields.
- Can be added to landing pages and the higher and lower content regions of standard pages.
- The javascript `high-school-search.js` can be found [here](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/blob/dev/public/themes/custom/hdbt_subtheme/src/js/high-school-search.js).

#### <a name="playground-search"></a>Playground search (playground_search)

This search paragraph lists TPR units that are tagged with the `unit_type` vocabulary term _Playground_.

- The filter search is a _view_ (`playground_search`) with exposed filters.
- No React front.
- The paragraph has editable title and description fields
- Can be added to landing pages and lower content regions of standard pages.

#### School search (school_search)

The _School search_ paragraph provides a tabbed interface that allows users to either find the nearest school by
entering a street address or find any school by filtering through the school's information. The search results are
displayed in a tabbed format, offering both a list view and a map view.

This search functionality is built with React and utilizes a views listing (`comprehensive_school_search`) as a
fallback when JavaScript is disabled. All React-based searches are located in the `hdbt` theme, where most of the
related logic is implemented.

- The fallback listing is a _view_ called (`comprehensive_school_search`) and it doesn't have any filters. The fallback
view configuration can be found in [here](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/blob/dev/conf/cmi/views.view.comprehensive_school_search.yml).
- The search has a React front and the code can be found [here](https://github.com/City-of-Helsinki/drupal-hdbt/tree/main/src/js/react/apps/school-search).
- The paragraph has editable title and description fields.
- Can be added to landing pages.

#### <a name="vocational-school-search"></a>Vocational School Search (vocational_school_search)

The _Vocational school search_ was created based on the [High school search](#high-school-search), so it has
similarities in the implementation details.

The units are selected manually in the paragraph field `field_vs_search_units`, and the view filters the TPR units to
display based on the field's content. It is also possible to change the form's submit button text by writing the desired
text in the `field_vs_search_meta_button` field.

- The filter search is a _view_ (`vocational_school_search`) with exposed filter. The view configuration can be found in
[here](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/blob/dev/conf/cmi/views.view.vocational_school_search.yml).
- No React front.
- The paragraph has editable title, description, units and search button fields.
- Can be added to landing pages and the higher and lower content regions of standard pages.

### Custom roles

#### Daycare editor

This user role grants administrative access to _daycare_ TPR units on the site. Permissions are provided by the
`helfi_kasko_content` module. Users can enrich TPR unit content categorized under `daycare`. The category assignment is
based on the `ontologyword_ids` associated with the TPR units. Read more about TPR unit categorization [here](#tpr-unit-categorization).

#### Playground editor

This user role grants administrative access to _playground_ TPR units on the site. Permissions are provided by the
`helfi_kasko_content` module. Users can enrich TPR unit content categorized under `playground`. The category assignment
is based on the `ontologyword_ids` associated with the TPR units. Read more about TPR unit categorization [here](#tpr-unit-categorization).

#### Comprehensive school editor

This user role grants administrative access to _comprehensive school_ TPR units on the site. Permissions are provided
by the `helfi_kasko_content` module. Users can enrich TPR unit content categorized under `comprehensive school`. The
category assignment is based on the `ontologyword_ids` associated with the TPR units. Read more about TPR unit
categorization [here](#tpr-unit-categorization).

#### Upper secondary school editor

This user role is designated for the _upper secondary school editor_. However, it does not provide direct permissions to
edit nodes. To gain these permissions, users must be assigned to an upper secondary school group, which grants the
necessary editing rights for nodes within the group. This group grants other required permissions such as to use media
entities and access to TPR units and services entities. More information about groups can be found [here](#groups).

### <a name="groups"></a>Groups

Unlike other instances, KASKO has the Group contrib module enabled. This module restricts upper secondary school
editors’ access to a specific set of nodes and allows them to create dedicated website for their school within the KASKO
instance. Groups are used only for upper secondary schools and the groups can have standard pages, landing pages,
news items and announcements inside the group content.

There is also a custom module called `helfi_group` that modifies group menus, alters the standard translation features
for nodes under group control, and allows TPR unit entities to be managed within the group.

Since groups can have their own news items, the news feature is enabled in the KASKO instance but is limited to upper
secondary schools. Each school can publish its own news, which is displayed within the school’s specific area. Other
KASKO-related news is created in the Etusivu instance in the same manner as other news, and the enabled news feature
does not affect this process.

- The custom module `helfi_group` can be found from [here](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/tree/dev/public/modules/custom/helfi_group).

#### Common issues

The list of all groups (/admin/group) is empty even there is multiple groups in the database:

Only users with _Administrator role_ (not even Super administrators currently) are able to see all groups and assign
members to groups that they are not members themselves. If you are a member of a certain group, but you don't have the
Administrator role, you can see the group that you belong to on the listing.

### Group news

Each of the upper secondary school groups can have news specific to their school. The news items are functionally the
same as those used in the Etusivu instance, but they are limited to the school's designated area. News is displayed in
two different paragraphs: [Group News](#group-news) and [Group News Archive](#group-news-archive).

News items also have the _Override Node Options_ feature enabled, which is not part of the standard installation. This
allows _upper secondary school editors_ to override the _News Item Published_ option without needing permissions to
create news items outside the group they belong to. The override node options functionality is provided by the
`override_node_options` contrib module.

### Group menus

Each upper secondary school (high school) has their own group menu. This group menu is used on subpages of the school,
and it can be edited by the group members under the group configuration. The basic functionality of the Group modules
group menu has been customized in the `helfi_group` custom module. The group menu is displayed using a block that is
assigned to the _First sidebar_ region and has been restricted to _Group type Upper secondary school_.

- Read more about the customizations [here](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/tree/dev/public/modules/custom/helfi_group).

## Customizations

### TPR unit template overrides

The basic TPR unit template is overridden in `hdbt_subtheme` so that different kinds of school cards are rendered in
a specific way. You can check the template in [here](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/blob/dev/public/themes/custom/hdbt_subtheme/templates/module/helfi_tpr/tpr-unit.html.twig).

Also the TPR unit ontology word details template is overridden in `hdbt_subtheme` in a more detailed way. Check out the
template [here](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/blob/dev/public/themes/custom/hdbt_subtheme/templates/module/helfi_tpr/tpr-unit-ontologyword-details.html.twig).

### <a name="tpr-unit-categorization"></a>TPR unit categorization

In KASKO instance the TPR units are categorized based on some of the ontology IDs they have in the TPR API. The
categories are then saved to a field called [Categories](#categories) in the `helfi_kasko_content` custom module in
[this file](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/blob/dev/public/modules/custom/helfi_kasko_content/src/UnitCategoryUtility.php).
The categories are then used on role assignment based on unit category and as search filters.

- Read more about the implementation from the `helfi_kasko_content` custom module [here](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/tree/dev/public/modules/custom/helfi_kasko_content).

### TPR unit custom fields

Unlike other instances, KASKO has some additional fields on TPR units.

#### <a name="categories"></a>Categories (field_categories)

Field categories is used to save the TPR unit categorization that is done based on the `ontologyword_ids`. Read more
about it in the [TPR unit categorization section](#tpr-unit-categorization).

#### [DEPRECATED] High school front page (field_hs_front_page)

Reference field where the upper secondary school (high school) front page used to be added. Appears to be unused and
possibly **deprecated** field.

#### Ontologyword details (field_ontologyword_details)

This field is used to save _language program and weighted curriculum education_ information for TPR units. If the
information is present it is shown on the TPR unit entity page. This field is also used in filtering the
[High school search](#high-school-search) results. The field rendering is done in the `hdbt` theme [here](https://github.com/City-of-Helsinki/drupal-hdbt/blob/main/templates/module/helfi_tpr/tpr-unit.html.twig).

#### Study field (field_study_field)

Taxonomy term reference field that is used to categorize the vocational schools. The field is used in filtering the
[Vocational school search](#vocational-school-search).

### TPR unit fields that are not use elsewhere

There are also fields that are available globally for all the instances to use, but are used only in KASKO. This can
however change if some of the other instances would for some reason need to functionality.

#### Hide description (hide_description)

This is a simple boolean field that determines if _Description_ field is shown on the TPR unit page. The field is used
in the `hdbt` theme [here](https://github.com/City-of-Helsinki/drupal-hdbt/blob/main/templates/module/helfi_tpr/tpr-unit.html.twig).

#### Contact details of daycare centre groups (subgroup)

As the human-readable name suggests, this field includes _contact details of daycare centre groups_. The information is
displayed on the TPR unit page if there is data in the field and the unit is of type _daycare_. The field rendering is
done in the `hdbt` theme [here](https://github.com/City-of-Helsinki/drupal-hdbt/blob/main/templates/module/helfi_tpr/tpr-unit.html.twig).

### News item pattern

The news item pattern in KASKO is customized compared to the standard format. In KASKO, news items are always related to
an upper secondary school and are displayed within the school’s designated area. The pattern is designed to accommodate
this setup. The customization is done in custom module called `helfi_kasko_config`. You can check the code in [here](https://github.com/City-of-Helsinki/drupal-helfi-kasvatus-koulutus/tree/dev/public/modules/custom/helfi_kasko_config).
