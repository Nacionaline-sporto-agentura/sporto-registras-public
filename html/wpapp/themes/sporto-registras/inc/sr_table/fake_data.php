<?php
// Funkcija, kuri sugeneruoja atsitiktinį pašto kodą
function generateRandomPostcode() {
    return str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
}

// Funkcija, kuri sugeneruoja atsitiktinį savivaldybės pavadinimą
function generateRandomMunicipality() {
    $municipalities = [
        "Alytaus m. sav.", "Alytaus r. sav.", "Anykščių r. sav.", "Birštono sav.", "Biržų r. sav.",
        "Druskininkų sav.", "Elektrėnų sav.", "Ignalinos r. sav.", "Jonavos r. sav.", "Joniškio r. sav.",
        "Jurbarko r. sav.", "Kaišiadorių r. sav.", "Kalvarijos sav.", "Kauno m. sav.", "Kauno r. sav.",
        "Kazlų Rūdos sav.", "Kėdainių r. sav.", "Kelmės r. sav.", "Klaipėdos m. sav.", "Klaipėdos r. sav.",
        "Kretingos r. sav.", "Kupiškio r. sav.", "Lazdijų r. sav.", "Marijampolės sav.", "Mažeikių r. sav.",
        "Molėtų r. sav.", "Neringos sav.", "Pagėgių sav.", "Pakruojo r. sav.", "Palangos m. sav.",
        "Panevėžio m. sav.", "Panevėžio r. sav.", "Pasvalio r. sav.", "Plungės r. sav.", "Prienų r. sav.",
        "Radviliškio r. sav.", "Raseinių r. sav.", "Rietavo sav.", "Rokiškio r. sav.", "Skuodo r. sav.",
        "Šakių r. sav.", "Šalčininkų r. sav.", "Šiaulių m. sav.", "Šiaulių r. sav.", "Šilalės r. sav.",
        "Šilutės r. sav.", "Širvintų r. sav.", "Švenčionių r. sav.", "Tauragės r. sav.", "Telšių r. sav.",
        "Trakų r. sav.", "Ukmergės r. sav.", "Utenos r. sav.", "Varėnos r. sav.", "Vilkaviškio r. sav.",
        "Vilniaus m. sav.", "Vilniaus r. sav.", "Visagino sav.", "Zarasų r. sav."
    ];
    return $municipalities[array_rand($municipalities)];
}

// Funkcija, kuri sugeneruoja atsitiktinį sporto bazės tipą
function generateRandomSportbaseType() {
    $types = ['Aerodromai', 'Automobilių trasos', 'Motociklų trasos', 'Futbolo aikštės', 'Krepšinio aikštės'];
    return $types[array_rand($types)];
}

function generateRandomOrganizationType() {
    $types = ['Sporto organizacijos tipas', 'Fizinio aktyvumo veiklas vykdanti organizacija', 'Savivaldybės sporto ir (ar) švietimo įstaiga', 'Skėtinė sporto organizacija', 'Sporto klubas', 'Sporto šakos federacija', 'Kita'];
    return $types[array_rand($types)];
}

// Funkcija, kuri sugeneruoja atsitiktinį sporto šakos pavadinimą
function generateRandomSportName() {
    $sports = ['Krepšinis', 'Futbolas', 'Plaukimas', 'Tenisas', 'Lengvoji atletika', 'Golfas', 'Volejbolas'];
    return $sports[array_rand($sports)];
}

// Funkcija, sukurianti atsitiktinį sporto organizacijos pavadinimą
function generateRandomSportbaseName() {
    $names = ['Sporto baze', 'Sporto centras', 'Sporto mokykla', 'Sporto baze', 'Sporto centras', 'Sporto mokykla'];
    return $names[array_rand($names)];
}
function generateRandomSportOrganizationName() {
    $names = ['Sporto klubas', 'Sporto centras', 'Sporto mokykla', 'Sporto klubas', 'Sporto centras', 'Sporto mokykla', 'Lietuvos sporto centras'];
    return $names[array_rand($names)];
}

// Funkcija, kuri sugeneruoja atsitiktinį sporto organizacijos įrašą
function generateRandomOrganizations($id, $count) {
    $totalSports = 7;
    $sportIds = range(1, $totalSports);
    shuffle($sportIds); 

    $selectedSports = array_slice($sportIds, 0, rand(1, 3));
    
    return [
        "id" => $id,
        "name" => generateRandomSportOrganizationName()." $id",
        "address" => "Ozo g. " . rand(1, $count) . ", LT-" . generateRandomPostcode() . " " . generateRandomMunicipality(),
        "type" => [
            "id" => rand(1, 5),
            "name" => generateRandomOrganizationType()
        ],
        "sports" => array_map(function ($sportId) {
            return [
                "id" => $sportId,
                "name" => generateRandomSportName()
            ];
        }, $selectedSports),
        "support" => rand(0, 1),
        "nvo" => rand(0, 1),
        "nvs" => rand(0, 1),
    ];
}

function generateRandomSportsbases($id, $count) {
    $totalSports = 7;
    $sportIds = range(1, $totalSports);
    shuffle($sportIds); 

    $selectedSports = array_slice($sportIds, 0, rand(1, 3));
    
    return [
        "id" => $id,
        "name" => generateRandomSportbaseName()." $id",
        "tenant" => [
            "id" => rand(1, 5),
            "name" => generateRandomSportOrganizationName()
        ],
        "municipality" => [
            "code" => rand(1, $count),
            "name" => generateRandomMunicipality()
        ],
        "type" => [
            "id" => rand(1, 5),
            "name" => generateRandomSportbaseType()
        ],
        "spacesCount" => rand(0, 1),
        "accessibility" => rand(0, 1),
        "sportTypes" => array_map(function ($sportId) {
            return [
                "id" => $sportId,
                "name" => generateRandomSportName()
            ];
        }, $selectedSports)
    ];
}
function generateRandomSportPersons($id, $count) {
    return [
        "sportTypeName" => generateRandomSportName(),
        "coach" => rand(0, $count),
        "referee" => rand(0, $count),
        "amsInstructor" => rand(0, $count),
        "faSpecialist" => rand(0, $count),
        "faInstructor" => rand(0, $count),
        "athlete" => rand(0, $count),
    ];
}
function renderSportbasesDataset($count = 100) {
    $data = [];
    for ($i = 1; $i <= $count; $i++) {
        $data[] = generateRandomSportsbases($i, $count);
    }
    return $data;
}
function renderOrganizationsDataset($count = 100) {
    $data = [];
    for ($i = 1; $i <= $count; $i++) {
        $data[] = generateRandomOrganizations($i, $count);
    }
    return $data;
}
function renderSportPersonsDataset($count = 100) {
    $data = [];
    for ($i = 1; $i <= $count; $i++) {
        $data[] = generateRandomSportPersons($i, $count);
    }
    return $data;
}
