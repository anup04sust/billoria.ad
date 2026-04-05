Here’s a clean **Drupal content model sheet** for your billboard project, covering both **content types** and **taxonomy vocabularies**.

---

# Drupal Content Model Sheet

## 1) Content Type: `billboard`

Primary inventory unit for a single billboard or display asset.

| Field Label             | Machine Name                |     Field Type | Required | Multiple | Reference / Allowed Values               | Notes                          |
| ----------------------- | --------------------------- | -------------: | :------: | :------: | ---------------------------------------- | ------------------------------ |
| Title                   | `title`                     |          Title |    Yes   |    No    | —                                        | Billboard display name         |
| Billboard ID            | `field_billboard_id`        |     Plain text |    Yes   |    No    | —                                        | Internal unique code           |
| Media Format            | `field_media_format`        | Term reference |    Yes   |    No    | `media_format`                           | Static, LED, Unipole, etc.     |
| Placement Type          | `field_placement_type`      | Term reference |    Yes   |    No    | `placement_type`                         | Road divider, rooftop, etc.    |
| Road Name               | `field_road_name`           | Term reference |    Yes   |    No    | `road_name`                              | Main road/corridor             |
| Road Type               | `field_road_type`           | Term reference |    Yes   |    No    | `road_type`                              | National, Regional, City, etc. |
| Division                | `field_division`            | Term reference |    Yes   |    No    | `division`                               | Bangladesh division            |
| District                | `field_district`            | Term reference |    Yes   |    No    | `district`                               | District                       |
| Upazila / Thana         | `field_upazila_thana`       | Term reference |    No    |    No    | `upazila_thana`                          | Sub-district                   |
| City Corporation        | `field_city_corporation`    | Term reference |    No    |    No    | `city_corporation`                       | Urban inventory                |
| Area / Zone             | `field_area_zone`           | Term reference |    Yes   |    No    | `area_zone`                              | Hyperlocal commercial area     |
| Traffic Direction       | `field_traffic_direction`   | Term reference |    No    |    No    | `traffic_direction`                      | Inbound, outbound, both        |
| Visibility Class        | `field_visibility_class`    | Term reference |    No    |    No    | `visibility_class`                       | Premium, high, medium          |
| Illumination Type       | `field_illumination_type`   | Term reference |    No    |    No    | `illumination_type`                      | Front lit, LED, etc.           |
| Booking Mode            | `field_booking_mode`        | Term reference |    Yes   |    No    | `booking_mode`                           | Full, cluster, SOV, etc.       |
| Availability Status     | `field_availability_status` | Term reference |    Yes   |    No    | `availability_status`                    | Available, booked, etc.        |
| Audience Type           | `field_audience_type`       | Term reference |    No    |    Yes   | `audience_type`                          | Optional Phase 2               |
| Advertiser Category     | `field_advertiser_category` | Term reference |    No    |    Yes   | `advertiser_category`                    | Optional Phase 2               |
| Latitude                | `field_latitude`            |        Decimal |    Yes   |    No    | —                                        | GPS latitude                   |
| Longitude               | `field_longitude`           |        Decimal |    Yes   |    No    | —                                        | GPS longitude                  |
| Facing Direction        | `field_facing_direction`    |    List (text) |    No    |    No    | North, South, East, West, NE, NW, SE, SW | Can also use angle later       |
| Visibility Distance (m) | `field_visibility_distance` |        Integer |    No    |    No    | —                                        | Estimated visibility           |
| Width (ft)              | `field_width_ft`            |        Decimal |    No    |    No    | —                                        | Billboard size                 |
| Height (ft)             | `field_height_ft`           |        Decimal |    No    |    No    | —                                        | Billboard size                 |
| Display Size Text       | `field_display_size`        |     Plain text |    No    |    No    | —                                        | Example: 10x20 ft              |
| Lane Count              | `field_lane_count`          |        Integer |    No    |    No    | —                                        | Nearby road condition          |
| Has Divider             | `field_has_divider`         |        Boolean |    No    |    No    | —                                        | Useful for divider inventory   |
| Commercial Score        | `field_commercial_score`    |        Integer |    No    |    No    | 1–100                                    | Internal ranking               |
| Traffic Score           | `field_traffic_score`       |        Integer |    No    |    No    | 1–100                                    | Internal ranking               |
| Rate Card Price         | `field_rate_card_price`     |        Decimal |    No    |    No    | —                                        | Base commercial rate           |
| Currency                | `field_currency`            |    List (text) |    No    |    No    | BDT, USD                                 | Usually BDT                    |
| Owner / Vendor Name     | `field_owner_vendor_name`   |     Plain text |    No    |    No    | —                                        | Agency / owner                 |
| Contact Number          | `field_contact_number`      |     Plain text |    No    |    No    | —                                        | Vendor contact                 |
| Is Premium              | `field_is_premium`          |        Boolean |    No    |    No    | —                                        | Highlight premium assets       |
| Active                  | `field_is_active`           |        Boolean |    Yes   |    No    | —                                        | Published/usable asset flag    |
| Hero Image              | `field_hero_image`          |          Image |    No    |    No    | —                                        | Main billboard image           |
| Gallery                 | `field_gallery`             |          Image |    No    |    Yes   | —                                        | Multiple supporting photos     |
| Notes                   | `field_notes`               |      Long text |    No    |    No    | —                                        | Internal comments              |

---

## 2) Content Type: `billboard_cluster`

Used for grouped roadside/divider inventory sold together.

| Field Label         | Machine Name                |       Field Type | Required | Multiple | Reference / Allowed Values | Notes                           |
| ------------------- | --------------------------- | ---------------: | :------: | :------: | -------------------------- | ------------------------------- |
| Title               | `title`                     |            Title |    Yes   |    No    | —                          | Cluster name                    |
| Cluster ID          | `field_cluster_id`          |       Plain text |    Yes   |    No    | —                          | Unique internal code            |
| Road Name           | `field_road_name`           |   Term reference |    Yes   |    No    | `road_name`                | Main corridor                   |
| Road Type           | `field_road_type`           |   Term reference |    Yes   |    No    | `road_type`                |                                 |
| Division            | `field_division`            |   Term reference |    Yes   |    No    | `division`                 |                                 |
| District            | `field_district`            |   Term reference |    Yes   |    No    | `district`                 |                                 |
| Upazila / Thana     | `field_upazila_thana`       |   Term reference |    No    |    No    | `upazila_thana`            | Sub-district                    |
| Area / Zone         | `field_area_zone`           |   Term reference |    Yes   |    No    | `area_zone`                |                                 |
| Placement Type      | `field_placement_type`      |   Term reference |    Yes   |    No    | `placement_type`           | Often Road Divider / Median     |
| Booking Mode        | `field_booking_mode`        |   Term reference |    Yes   |    No    | `booking_mode`             | Cluster booking                 |
| Availability Status | `field_availability_status` |   Term reference |    Yes   |    No    | `availability_status`      |                                 |
| Total Units         | `field_total_units`         |          Integer |    Yes   |    No    | —                          | Number of billboards in cluster |
| Billboards          | `field_billboards`          | Entity reference |    No    |    Yes   | `billboard`                | Child billboards                |
| Cluster Length (m)  | `field_cluster_length_m`    |          Decimal |    No    |    No    | —                          | Optional                        |
| Commercial Score    | `field_commercial_score`    |          Integer |    No    |    No    | 1–100                      |                                 |
| Rate Card Price     | `field_rate_card_price`     |          Decimal |    No    |    No    | —                          | Cluster rate                    |
| Hero Image          | `field_hero_image`          |            Image |    No    |    No    | —                          |                                 |
| Notes               | `field_notes`               |        Long text |    No    |    No    | —                          |                                 |

---

## 3) Content Type: `advertiser`

Optional but useful for bookings, reports, and CRM-lite workflows.

| Field Label         | Machine Name                |     Field Type | Required | Multiple | Reference / Allowed Values | Notes                   |
| ------------------- | --------------------------- | -------------: | :------: | :------: | -------------------------- | ----------------------- |
| Title               | `title`                     |          Title |    Yes   |    No    | —                          | Brand / advertiser name |
| Advertiser Category | `field_advertiser_category` | Term reference |    No    |    No    | `advertiser_category`      |                         |
| Contact Person      | `field_contact_person`      |     Plain text |    No    |    No    | —                          |                         |
| Email               | `field_email`               |          Email |    No    |    No    | —                          |                         |
| Phone               | `field_phone`               |     Plain text |    No    |    No    | —                          |                         |
| Website             | `field_website`             |           Link |    No    |    No    | —                          |                         |
| Notes               | `field_notes`               |      Long text |    No    |    No    | —                          |                         |

---

## 4) Content Type: `booking_inquiry`

For lead capture and future sales workflow.

| Field Label          | Machine Name                 |       Field Type | Required | Multiple | Reference / Allowed Values                     | Notes                  |
| -------------------- | ---------------------------- | ---------------: | :------: | :------: | ---------------------------------------------- | ---------------------- |
| Title                | `title`                      |            Title |    Yes   |    No    | —                                              | Inquiry title          |
| Inquiry ID           | `field_inquiry_id`           |       Plain text |    Yes   |    No    | —                                              | Internal code          |
| Advertiser           | `field_advertiser`           | Entity reference |    No    |    No    | `advertiser`                                   |                        |
| Billboard            | `field_billboard`            | Entity reference |    No    |    Yes   | `billboard`                                    | One or more billboards |
| Billboard Cluster    | `field_billboard_cluster`    | Entity reference |    No    |    Yes   | `billboard_cluster`                            | Optional               |
| Status               | `field_status`               |      List (text) |    Yes   |    No    | New, Qualified, Proposal Sent, Confirmed, Lost | Simple workflow        |
| Requested Start Date | `field_requested_start_date` |             Date |    No    |    No    | —                                              |                        |
| Requested End Date   | `field_requested_end_date`   |             Date |    No    |    No    | —                                              |                        |
| Budget               | `field_budget`               |          Decimal |    No    |    No    | —                                              |                        |
| Message              | `field_message`              |        Long text |    No    |    No    | —                                              |                        |
| Sales Notes          | `field_sales_notes`          |        Long text |    No    |    No    | —                                              | Internal               |
| Assigned To          | `field_assigned_to`          | Entity reference |    No    |    No    | User                                           | Sales owner            |

---

# Taxonomy Vocabularies

## 1) Vocabulary: `road_type`

| Term Name              | Code                     | Notes                     |
| ---------------------- | ------------------------ | ------------------------- |
| National Highway       | `national_highway`       | Major intercity roads     |
| Regional Highway       | `regional_highway`       | Regional connectors       |
| Zila Road              | `zila_road`              | District roads            |
| City Road              | `city_road`              | Urban roads               |
| Bypass Road            | `bypass_road`            | Bypass corridors          |
| Expressway             | `expressway`             | Controlled fast corridors |
| Flyover                | `flyover`                | Elevated corridors        |
| Service Road           | `service_road`           | Parallel local access     |
| Link Road              | `link_road`              | Connector route           |
| Connector Road         | `connector_road`         | Short corridor link       |
| Bridge Approach Road   | `bridge_approach_road`   | Major bridge access       |
| Industrial Access Road | `industrial_access_road` | EPZ / industrial area     |
| Port Access Road       | `port_access_road`       | Port cargo areas          |
| Airport Access Road    | `airport_access_road`    | Airport approach          |
| Ring Road              | `ring_road`              | Ring corridors            |
| Outer Ring Road        | `outer_ring_road`        | Outer urban loop          |
| Inner Ring Road        | `inner_ring_road`        | Inner urban loop          |
| Commercial Corridor    | `commercial_corridor`    | Business-heavy route      |
| Urban Arterial Road    | `urban_arterial_road`    | Major city artery         |
| Urban Collector Road   | `urban_collector_road`   | Collector route           |

### Fields on taxonomy term

| Field Label | Machine Name        | Field Type |
| ----------- | ------------------- | ---------- |
| Code        | `field_code`        | Plain text |
| Description | `field_description` | Long text  |

---

## 2) Vocabulary: `road_name`

### Fields on taxonomy term

| Field Label      | Machine Name             | Field Type     | Reference / Notes            |
| ---------------- | ------------------------ | -------------- | ---------------------------- |
| Road Code        | `field_road_code`        | Plain text     | Example: N1                  |
| Road Type        | `field_road_type`        | Term reference | `road_type`                  |
| Division         | `field_division`         | Term reference | `division`                   |
| District         | `field_district`         | Term reference | `district`                   |
| Upazila / Thana  | `field_upazila_thana`    | Term reference | `upazila_thana`, optional    |
| City Corporation | `field_city_corporation` | Term reference | `city_corporation`, optional |
| Area / Zone      | `field_area_zone`        | Term reference | `area_zone`, optional        |
| Priority Tier    | `field_priority_tier`    | List (text)    | Tier 1, Tier 2, Tier 3       |
| Commercial Score | `field_commercial_score` | Integer        | 1–100                        |
| Active           | `field_is_active`        | Boolean        |                              |
| Notes            | `field_notes`            | Long text      |                              |
| OSM Ref          | `field_osm_ref`          | Plain text     | Optional                     |

### Seed terms

| Term Name                        | Road Code | Road Type              |
| -------------------------------- | --------- | ---------------------- |
| Dhaka–Chattogram Highway         | N1        | National Highway       |
| Dhaka–Sylhet Highway             | N2        | National Highway       |
| Dhaka–Mymensingh Highway         | N3        | National Highway       |
| Dhaka–Aricha Highway             | N4        | National Highway       |
| Dhaka–Rangpur Highway            | N5        | National Highway       |
| Dhaka–Rajshahi Highway           | N6        | National Highway       |
| Daulatdia–Khulna Highway         | N7        | National Highway       |
| Dhaka–Barishal Highway           | N8        | National Highway       |
| Chattogram–Cox’s Bazar Highway   | N102      | National Highway       |
| Dhaka–Chandpur Highway           | N104      | National Highway       |
| Mymensingh–Netrokona Highway     | N105      | National Highway       |
| Rangpur–Burimari Highway         | N106      | National Highway       |
| Khulna–Satkhira Highway          | N110      | National Highway       |
| Sylhet–Tamabil Highway           | N112      | National Highway       |
| Sylhet–Sunamganj Highway         | N117      | National Highway       |
| Tangail–Jamalpur Highway         | N118      | National Highway       |
| Bogura–Rangpur Highway           | N119      | National Highway       |
| Airport Road                     | —         | City Road              |
| Mirpur Road                      | —         | City Road              |
| Pragati Sarani                   | —         | City Road              |
| Gulshan Avenue                   | —         | City Road              |
| Kemal Ataturk Avenue             | —         | City Road              |
| Rokeya Sarani                    | —         | City Road              |
| DIT Road                         | —         | City Road              |
| VIP Road                         | —         | City Road              |
| Inner Circular Road              | —         | Inner Ring Road        |
| Outer Circular Road              | —         | Outer Ring Road        |
| Madani Avenue                    | —         | City Road              |
| Purbachal Expressway             | —         | Expressway             |
| 300 Feet Road                    | —         | Urban Arterial Road    |
| Abdullahpur–Ashulia Road         | —         | Link Road              |
| Bijoy Sarani                     | —         | City Road              |
| Kazi Nazrul Islam Avenue         | —         | City Road              |
| Satmasjid Road                   | —         | City Road              |
| Panthapath                       | —         | City Road              |
| Green Road                       | —         | City Road              |
| New Eskaton Road                 | —         | City Road              |
| Hatirjheel Peripheral Road       | —         | Urban Arterial Road    |
| CDA Avenue                       | —         | City Road              |
| Sheikh Mujib Road                | —         | City Road              |
| Port Connecting Road             | —         | Port Access Road       |
| Agrabad Access Road              | —         | Commercial Corridor    |
| Bayezid Bostami Road             | —         | City Road              |
| Chattogram Airport Road          | —         | Airport Access Road    |
| Khulna–Jessore Road              | —         | Regional Highway       |
| Khan Jahan Ali Road              | —         | City Road              |
| Boyra Main Road                  | —         | City Road              |
| Sonadanga Main Road              | —         | City Road              |
| Sylhet–Tamabil Road              | —         | Regional Highway       |
| Airport Road Sylhet              | —         | Airport Access Road    |
| Amberkhana Corridor              | —         | Commercial Corridor    |
| Airport Road Rajshahi            | —         | Airport Access Road    |
| Greater Road                     | —         | City Road              |
| Talaimari Corridor               | —         | Commercial Corridor    |
| Barishal–Bhola Road              | —         | Regional Highway       |
| Sadar Road Barishal              | —         | City Road              |
| Nathullabad Corridor             | —         | Commercial Corridor    |
| Gazipur–Tongi Corridor           | —         | Commercial Corridor    |
| Narayanganj Link Road            | —         | Link Road              |
| Mawa Approach Road               | —         | Bridge Approach Road   |
| Bangabandhu Bridge Approach Road | —         | Bridge Approach Road   |
| EPZ Access Road Chattogram       | —         | Industrial Access Road |
| Mongla Port Access Road          | —         | Port Access Road       |

---

## 3) Vocabulary: `division`

| Term Name  |
| ---------- |
| Dhaka      |
| Chattogram |
| Rajshahi   |
| Khulna     |
| Barishal   |
| Sylhet     |
| Rangpur    |
| Mymensingh |

No extra fields needed for MVP.

---

## 4) Vocabulary: `district`

### Fields on taxonomy term

| Field Label | Machine Name     | Field Type     |
| ----------- | ---------------- | -------------- |
| Division    | `field_division` | Term reference |
| Geo Code    | `field_geo_code` | Plain text     |

### Seed terms

| District    | Division   |
| ----------- | ---------- |
| Dhaka       | Dhaka      |
| Gazipur     | Dhaka      |
| Narayanganj | Dhaka      |
| Narsingdi   | Dhaka      |
| Tangail     | Dhaka      |
| Faridpur    | Dhaka      |
| Chattogram  | Chattogram |
| Cox’s Bazar | Chattogram |
| Cumilla     | Chattogram |
| Sylhet      | Sylhet     |
| Khulna      | Khulna     |
| Jessore     | Khulna     |
| Bagerhat    | Khulna     |
| Satkhira    | Khulna     |
| Rajshahi    | Rajshahi   |
| Bogura      | Rajshahi   |
| Rangpur     | Rangpur    |
| Mymensingh  | Mymensingh |
| Barishal    | Barishal   |

---

## 5) Vocabulary: `upazila_thana`

### Fields on taxonomy term

| Field Label | Machine Name     | Field Type     |
| ----------- | ---------------- | -------------- |
| District    | `field_district` | Term reference |
| Geo Code    | `field_geo_code` | Plain text     |
| Type        | `field_type`     | List (text)    |

**Field `field_type` allowed values**: Upazila, Thana (Metropolitan), Pourashava

### Seed terms (Examples - expand based on needs)

| Upazila/Thana          | District    | Type                |
| ---------------------- | ----------- | ------------------- |
| Gulshan Thana          | Dhaka       | Thana (Metropolitan)|
| Motijheel Thana        | Dhaka       | Thana (Metropolitan)|
| Tejgaon Thana          | Dhaka       | Thana (Metropolitan)|
| Mirpur Thana           | Dhaka       | Thana (Metropolitan)|
| Uttara Thana           | Dhaka       | Thana (Metropolitan)|
| Savar Upazila          | Dhaka       | Upazila             |
| Keraniganj Upazila     | Dhaka       | Upazila             |
| Ashulia                | Dhaka       | Upazila             |
| Tongi                  | Gazipur     | Upazila             |
| Kaliakair              | Gazipur     | Upazila             |
| Kapasia                | Gazipur     | Upazila             |
| Rupganj                | Narayanganj | Upazila             |
| Sonargaon              | Narayanganj | Upazila             |
| Araihazar              | Narayanganj | Upazila             |
| Panchlaish Thana       | Chattogram  | Thana (Metropolitan)|
| Kotwali Thana          | Chattogram  | Thana (Metropolitan)|
| Pahartali Thana        | Chattogram  | Thana (Metropolitan)|
| Agrabad Thana          | Chattogram  | Thana (Metropolitan)|
| Cox's Bazar Sadar      | Cox's Bazar | Upazila             |
| Teknaf                 | Cox's Bazar | Upazila             |
| Ramu                   | Cox's Bazar | Upazila             |
| Khulna Sadar           | Khulna      | Upazila             |
| Daulatpur              | Khulna      | Upazila             |
| Khalishpur             | Khulna      | Thana (Metropolitan)|
| Sylhet Sadar           | Sylhet      | Upazila             |
| South Surma            | Sylhet      | Upazila             |
| Companiganj            | Sylhet      | Upazila             |
| Rajshahi Sadar         | Rajshahi    | Upazila             |
| Boalia Thana           | Rajshahi    | Thana (Metropolitan)|
| Motihar Thana          | Rajshahi    | Thana (Metropolitan)|
| Rangpur Sadar          | Rangpur     | Upazila             |
| Mymensingh Sadar       | Mymensingh  | Upazila             |
| Barishal Sadar         | Barishal    | Upazila             |

**Note**: Bangladesh has 495 upazilas. Add more as needed for coverage expansion.

---

## 6) Vocabulary: `city_corporation`

| Term Name                    |
| ---------------------------- |
| Dhaka North City Corporation |
| Dhaka South City Corporation |
| Chattogram City Corporation  |
| Khulna City Corporation      |
| Rajshahi City Corporation    |
| Sylhet City Corporation      |
| Barishal City Corporation    |
| Gazipur City Corporation     |
| Cumilla City Corporation     |
| Rangpur City Corporation     |
| Mymensingh City Corporation  |
| Narayanganj City Corporation |

---

## 7) Vocabulary: `area_zone`

### Fields on taxonomy term

| Field Label      | Machine Name             | Field Type     |
| ---------------- | ------------------------ | -------------- |
| Upazila / Thana  | `field_upazila_thana`    | Term reference |
| City Corporation | `field_city_corporation` | Term reference |
| District         | `field_district`         | Term reference |
| Priority Tier    | `field_priority_tier`    | List (text)    |

### Seed terms

| Area / Zone            | Suggested Parent Market |
| ---------------------- | ----------------------- |
| Gulshan                | Dhaka                   |
| Banani                 | Dhaka                   |
| Mohakhali              | Dhaka                   |
| Tejgaon                | Dhaka                   |
| Farmgate               | Dhaka                   |
| Shahbag                | Dhaka                   |
| Motijheel              | Dhaka                   |
| Uttara                 | Dhaka                   |
| Kuril                  | Dhaka                   |
| Badda                  | Dhaka                   |
| Rampura                | Dhaka                   |
| Malibagh               | Dhaka                   |
| Kakrail                | Dhaka                   |
| Mirpur                 | Dhaka                   |
| Gabtoli                | Dhaka                   |
| Dhanmondi              | Dhaka                   |
| Mohammadpur            | Dhaka                   |
| Paltan                 | Dhaka                   |
| Jatrabari              | Dhaka                   |
| Sayedabad              | Dhaka                   |
| Bashundhara            | Dhaka                   |
| Purbachal Gateway      | Dhaka                   |
| Abdullahpur            | Dhaka                   |
| Ashulia Link           | Dhaka                   |
| Tongi Gateway          | Gazipur                 |
| Agrabad                | Chattogram              |
| GEC                    | Chattogram              |
| New Market             | Chattogram              |
| Port Gate              | Chattogram              |
| Oxygen                 | Chattogram              |
| AK Khan                | Chattogram              |
| Patenga                | Chattogram              |
| Sonadanga              | Khulna                  |
| Boyra                  | Khulna                  |
| Shibbari               | Khulna                  |
| Daulatpur              | Khulna                  |
| Amberkhana             | Sylhet                  |
| Zindabazar             | Sylhet                  |
| Airport Belt           | Sylhet                  |
| Humayun Rashid Chattar | Sylhet                  |
| Talaimari              | Rajshahi                |
| Laxmipur               | Rajshahi                |
| Railgate               | Rajshahi                |
| Court Area             | Rajshahi                |

---

## 8) Vocabulary: `media_format`

| Term Name                |
| ------------------------ |
| Static Billboard         |
| Digital Billboard        |
| LED Screen               |
| Pole Sign                |
| Rooftop Sign             |
| Wall Sign                |
| Bridge Banner            |
| Foot Over Bridge Panel   |
| Median Panel             |
| Unipole                  |
| Gantry Billboard         |
| Transit Shelter Branding |
| Lamppost Branding        |
| Building Wrap            |
| Directional Signage      |

---

## 9) Vocabulary: `placement_type`

| Term Name           |
| ------------------- |
| Roadside Left       |
| Roadside Right      |
| Road Divider        |
| Median Strip        |
| Intersection Corner |
| Flyover Side        |
| Flyover Underpass   |
| Roundabout          |
| Bridge Side         |
| Toll Plaza Approach |
| Bus Stand Area      |
| Rail Crossing Area  |
| Market Front        |
| Building Facade     |
| Rooftop             |
| Entry Gate          |
| Exit Gate           |

---

## 10) Vocabulary: `traffic_direction`

| Term Name       |
| --------------- |
| One Way         |
| Two Way         |
| Inbound         |
| Outbound        |
| Both Directions |
| Northbound      |
| Southbound      |
| Eastbound       |
| Westbound       |

---

## 11) Vocabulary: `visibility_class`

| Term Name |
| --------- |
| Premium   |
| High      |
| Medium    |
| Standard  |
| Limited   |

---

## 12) Vocabulary: `illumination_type`

| Term Name       |
| --------------- |
| Non Illuminated |
| Front Lit       |
| Back Lit        |
| LED Illuminated |
| Flood Lit       |
| Solar Lit       |

---

## 13) Vocabulary: `booking_mode`

| Term Name            |
| -------------------- |
| Full Unit Booking    |
| Partial Unit Booking |
| Cluster Booking      |
| Road Takeover        |
| Time Slot Booking    |
| Share of Voice       |
| Day Part Booking     |

---

## 14) Vocabulary: `availability_status`

| Term Name               |
| ----------------------- |
| Available               |
| Reserved                |
| Booked                  |
| Under Maintenance       |
| Temporarily Unavailable |
| Blocked                 |
| Archived                |

---

## 15) Vocabulary: `advertiser_category`

| Term Name               |
| ----------------------- |
| FMCG                    |
| Telecom                 |
| Real Estate             |
| Banking                 |
| Fintech                 |
| E-commerce              |
| Education               |
| Healthcare              |
| Automobile              |
| Electronics             |
| Fashion                 |
| Food and Beverage       |
| Government Campaign     |
| NGO / Development       |
| Media and Entertainment |
| Local Retail            |

---

## 16) Vocabulary: `audience_type`

| Term Name              |
| ---------------------- |
| Mass Commuters         |
| Office Commuters       |
| Premium Urban Audience |
| Family Audience        |
| Student Audience       |
| Tourist Audience       |
| Highway Travelers      |
| Industrial Workforce   |
| Port Traffic           |
| Mixed Audience         |

---

# Final MVP recommendation

For **Phase 1**, create these first:

* Content types:

  * `billboard`
  * `billboard_cluster`
  * `booking_inquiry`

* Taxonomies:

  * `road_type`
  * `road_name`
  * `division`
  * `district`
  * `upazila_thana` *(sub-district level)*
  * `city_corporation`
  * `area_zone`
  * `media_format`
  * `placement_type`
  * `traffic_direction`
  * `visibility_class`
  * `illumination_type`
  * `booking_mode`
  * `availability_status`

Then add `advertiser`, `advertiser_category`, and `audience_type` in Phase 2.

**Note**: `upazila_thana` provides crucial sub-district granularity. Bangladesh has ~495 upazilas; seed key metropolitan thanas and expand based on coverage needs.