# City of Helsinki - Kasko Content

## Kasko TPR Units

The Kasko school search index is built using TPR Units, which are imported via the [helfi_tpr](https://github.com/City-of-Helsinki/drupal-module-helfi-tpr?tab=readme-ov-file#drupal-tpr-integration) module. To ensure that only schools are included in the index, the [IsSchool](./src/Plugin/search_api/processor/IsSchool.php) Search API processor is used.

### How the IsSchool Search API Processor works

The **IsSchool** processor identifies schools by checking their assigned service IDs in the indexed TPR Units. It filters out any non-school entities.

#### Recognized school service IDs:
- **[3105](https://tpr.hel.fi/palvelukarttaws/rest/vpalvelurekisteri/description/3105?newfeatures=yes&format=xml)**
  - Translates to: *Luokkien 1-6 perusopetus* (Grades 1–6 Basic Education)
- **[3106](https://tpr.hel.fi/palvelukarttaws/rest/vpalvelurekisteri/description/3106?newfeatures=yes&format=xml)**
  - Translates to: *Luokkien 7-9 perusopetus* (Grades 7–9 Basic Education)

## Adding a new school to the index

To add a new school to the **Schools index**, it must first be registered in TPR by a TPR administrator or client. The required tags for a TPR Unit are:

- **"Suomenkielinen perusopetus luokille 1-6"** (Finnish-language basic education for grades 1–6)
- **"Suomenkielinen perusopetus luokille 7-9"** (Finnish-language basic education for grades 7–9)

These tags will be converted into the service IDs **3105** and **3106** in the TPR API.

### Handling schools that are not automatically indexed

In some cases, schools may not receive the correct service IDs in TPR, preventing them from appearing in the index. To manually adjust the indexing of such schools, use the **[School Settings](https://www.hel.fi/en/childhood-and-education/admin/config/school-settings)** page.
