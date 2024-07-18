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

#### After-school activity search (after_school_activity_search)

TBD

#### Daycare search (daycare_search)

TBD

#### Group news (group_news)

TBD

#### Group news archive (group_news_archive)

TBD

#### <a name="high-school-search"></a>High school search (high_school_search)

TBD

#### Playground search (playground_search)

TBD

#### School search (school_search)

TBD

#### <a name="vocational-school-search"></a>Vocational School Search (vocational_school_search)

TBD


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

TBD

### Group menus

TBD

## Customizations

### <a name="tpr-unit-categorization"></a>TPR unit categorization

TBD

### TPR unit custom fields

Unlike other instances, KASKO has some additional fields on TPR units.

#### Categories (field_categories)

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
