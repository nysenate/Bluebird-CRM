<?php

class CRM_NYSS_Resources_Resources
{
  /**
   * @param null $id
   * @return array|mixed|null
   *
   * county code lookup map
   */
  static function getCountyCodes($id = NULL) {
    $countyCodes = array(
      1 => 'Albany',
      2 => 'Allegany',
      3 => 'Broome',
      4 => 'Cattaraugus',
      5 => 'Cayuga',
      6 => 'Chautauqua',
      7 => 'Chemung',
      8 => 'Chenango',
      9 => 'Clinton',
      10 => 'Columbia',
      11 => 'Cortland',
      12 => 'Delaware',
      13 => 'Dutchess',
      14 => 'Erie',
      15 => 'Essex',
      16 => 'Franklin',
      17 => 'Fulton',
      18 => 'Genesee',
      19 => 'Greene',
      20 => 'Hamilton',
      21 => 'Herkimer',
      22 => 'Jefferson',
      23 => 'Lewis',
      24 => 'Livingston',
      25 => 'Madison',
      26 => 'Monroe',
      27 => 'Montgomery',
      28 => 'Nassau',
      29 => 'Niagara',
      30 => 'Oneida',
      31 => 'Onondaga',
      32 => 'Ontario',
      33 => 'Orange',
      34 => 'Orleans',
      35 => 'Oswego',
      36 => 'Otsego',
      37 => 'Putnam',
      38 => 'Rensselaer',
      39 => 'Rockland',
      40 => 'St. Lawrence',
      41 => 'Saratoga',
      42 => 'Schenectady',
      43 => 'Schoharie',
      44 => 'Schuyler',
      45 => 'Seneca',
      46 => 'Steuben',
      47 => 'Suffolk',
      48 => 'Sullivan',
      49 => 'Tioga',
      50 => 'Tompkins',
      51 => 'Ulster',
      52 => 'Warren',
      53 => 'Washington',
      54 => 'Wayne',
      55 => 'Westchester',
      56 => 'Wyoming',
      57 => 'Yates',
      60 => 'Bronx',
      61 => 'Kings',
      62 => 'New York',
      63 => 'Queens',
      64 => 'Richmond',
    );

    if (!empty($id)) {
      if (isset($countyCodes[$id])) {
        return $countyCodes[$id];
      }
      else {
        return $id;
      }
    }

    return $countyCodes;
  }
}
