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

#### Daycare search (daycare_search)

#### Group news (group_news)

#### Group news archive (group_news_archive)

#### High school search (high_school_search)

#### Playground search (playground_search)

#### School search (school_search)

#### Vocational School Search (vocational_school_search)


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

This user role grants administrative access to _comprehensive school_ TPR units on the site. Permissions are provided by the
`helfi_kasko_content` module. Users can enrich TPR unit content categorized under `comprehensive school`. The category assignment
is based on the `ontologyword_ids` associated with the TPR units. Read more about TPR unit categorization [here](#tpr-unit-categorization).

#### Upper secondary school editor


### Groups

### Group news

### Group menus

## Customizations

### <a name="tpr-unit-categorization"></a>TPR unit categorization

