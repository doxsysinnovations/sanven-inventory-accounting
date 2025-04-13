<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            ['name' => 'AMHSCO ENTERPRISES', 'trade_name' => 'AMHSCO ENTERPRISES', 'identification_number' => '000-265-811-001', 'address' => 'AMHSCO HOLDINGS INC. CORPORATE CTR. PUROK 1, BRGY. ALASAS CSFP'],
            ['name' => 'ALKEM', 'trade_name' => null, 'identification_number' => null, 'address' => null],
            ['name' => 'AQUINO, WILHELMINA VERGARA', 'trade_name' => 'XEM PHARMACEUTICAL', 'identification_number' => '184-459-415-000', 'address' => '802 ISABEL ST. SAN NICOLAS, POB. CONCEPCION TARLAC'],
            ['name' => 'BAUTUSTA, IVAN MERVERIX BUNDOC', 'trade_name' => 'IMED MEDICAL SUPPLIES', 'identification_number' => '604-822-859-000', 'address' => '194 SAN JOSE PAOMBONG BULACAN'],
            ['name' => 'BAUTISTA, MARVIN DIAZ', 'trade_name' => 'MDB PHARMA TRADING', 'identification_number' => '305-315-099-000', 'address' => '194 SITIO SAN JOSE PAOMBONG BULACAN'],
            ['name' => 'BLUESENSOR HEALTHCARE MARKETING SERVICES', 'trade_name' => 'BLUESENSOR HEALTHCARE MARKETING SERVICES', 'identification_number' => '730-151-134-000', 'address' => '#9 ROMANIA JADE ST., PYLONG GUBAT, GUIGUINTO, BULACAN'],
            ['name' => 'BRIXMED PHARMA CARE INC.', 'trade_name' => 'BRIXMED PHARMA CARE INC.', 'identification_number' => '009-742-209-000', 'address' => '#920 NARRA ST. BLUE DIAMOND SUBD. SAN VICENTE STO. TOMAS, PAMPANGA'],
            ['name' => 'B. BRAUN MEDICAL SUPPLIES INC', 'trade_name' => 'B. BRAUN MEDICAL SUPPLIES INC', 'identification_number' => '000-154-733-000', 'address' => '15/F SUNLIFE CTR. 5TH AVE. COR RIZAL DRIVE BONIFACIO GLOBAL CITY, TAGUIG'],
            ['name' => 'DELEX PHARMA INT\'L. INC.', 'trade_name' => 'DELEX PHARMA INT\'L. INC.', 'identification_number' => '007-326-772-000', 'address' => 'L4 B4 CARNATION COR MAGNOLIA ST. BRGY. SAUYO QC'],
            ['name' => 'DKHS MARKET EXPANSION SERVICES PHILIPPINES, INC.', 'trade_name' => 'DKHS MARKET EXPANSION SERVICES PHILIPPINES, INC.', 'identification_number' => '601-297-580-000', 'address' => '8TH FL, CYBER SIGMA, LAWTON AVENUE, MCKINLEY WEST, FORT BONIFACION, TAGUIG CITY'],
            ['name' => 'DUNGCA, RAYLYN TONDING', 'trade_name' => 'JOLLYMED ENTERPRISES', 'identification_number' => '203-499-232-000', 'address' => '1629 ALVARES ST. STA. CRUZ, MANILA'],
            ['name' => 'ENCINARES, DEO VINCENT CRUZ', 'trade_name' => 'QUADGEN PHARMACEUTICAL', 'identification_number' => '239-920-825-000', 'address' => '4496 (F) J. GARCIA POB. PLARIDEL BULACAN'],
            ['name' => 'FERJ\'S  PHARMACY', 'trade_name' => 'FERJ\'S  PHARMACY', 'identification_number' => '102-274-734-006', 'address' => '1534 SALVICTORIA CONDOMINIUM, RIZAL AVE. STA. CRUZ MANILA'],
            ['name' => 'GETZ BROS. PHILS. INC.', 'trade_name' => 'GETZ BROS. PHILS. INC.', 'identification_number' => '000-280-423-000', 'address' => 'BRGY. SAUYO QUEZON CITY'],
            ['name' => 'GLOBO ASIATICO ENTERPRISES INC.', 'trade_name' => 'GLOBO ASIATICO ENTERPRISES INC.', 'identification_number' => '202-482-112-00000', 'address' => '127 JDK BLDG. MAGINHAWA ST. TEACHERS VILLAGE, QUEZON CITY'],
            ['name' => 'GMED TRADERS CORPORATION', 'trade_name' => 'GMED TRADERS CORPORATION', 'identification_number' => '611-419-551-000', 'address' => '1613 ALVAREZ ST. BRGY. 325 ZONE 032 STA. CRUZ, NCR CITY OF MANILA 1ST DISTRICT'],
            ['name' => 'I.E. MEDICA INC.', 'trade_name' => 'I.E. MEDICA INC.', 'identification_number' => '007-723-870-000', 'address' => '5/F RFM CORPORATE PIONEER ST. MANDALAUYONG CITY'],
            ['name' => 'MACRO PHARMA CORP.', 'trade_name' => 'MACRO PHARMA INC.', 'identification_number' => '201-981-544-000', 'address' => 'CW HOME DEPOT #1, JULIA VARGAS AVE. UGONG DIST. 1 PASIG CITY'],
            ['name' => 'MAED PHARMA CORP', 'trade_name' => 'MAED PHARMA CORP', 'identification_number' => '238-306-412-000', 'address' => '7620 CHESTNUT ST. MARCELO GREEN VALLEY BRGY. MARCELO GREEN, PARANAQUE CITY'],
            ['name' => 'MEDECIA MED. INC.', 'trade_name' => 'MEDECIA MED. INC.', 'identification_number' => '211-759-563-000', 'address' => 'MOLAVE ST. AGUAS SUBD. MANIBAUG LIBUTAD, PORAC, PAMPANGA'],
            ['name' => 'MEDLANE PHARMA INC.', 'trade_name' => 'MEDLANE PHARMA INC.', 'identification_number' => '009-755-737-000', 'address' => 'BRGY. STA. TERESITA QUEZON CITY'],
            ['name' => 'METRO DRUG INC.', 'trade_name' => 'METRO DRUG INC.', 'identification_number' => '004-641-985-000', 'address' => 'STA. MARIA INDUSTRIAL ESTATE, MANALAC AVE. BAGUMBAYAN TAGUIG'],
            ['name' => 'OXFORD DISTRIBUTOR\'S INC', 'trade_name' => 'OXFORD DISTRIBUTOR\'S INC', 'identification_number' => '220-579-460-008', 'address' => 'UNIT E-1408 14/F EAST TOWER SECE ROAD ORTIGAS CENTER  SAN ANTONIO PASIG'],
            ['name' => 'PHILCARE PHARMA INC.', 'trade_name' => 'PHILCARE PHARMA INC.', 'identification_number' => '007-502-646-000', 'address' => '# 3 MAHOGANY ST. AGAPITO SUBD. SANTOLAN, PASIG CITY'],
            ['name' => 'PHIL PHARMAWEALTH INC. - PAMPANGA', 'trade_name' => 'PHIL PHARMAWEALTH INC. - PAMPANGA', 'identification_number' => '002-304-674-003', 'address' => 'PINHOLE BLDG. MC ARTHUR HIWAY, QUEBIAWAN CSFP'],
            ['name' => 'PROS MARKETING', 'trade_name' => 'PROS MARKETING', 'identification_number' => '000-864-633-001', 'address' => 'SAN ANGELO SUBD. STO. DOMINGO ANGELES CITY'],
            ['name' => 'SANDOVAL DISTRIBUTORS, INC', 'trade_name' => 'SANDOVAL DISTRIBUTORS, INC', 'identification_number' => '005-101-904-000', 'address' => 'SAN PEDRO WEST, ROSALES, PANGASINAN'],
            ['name' => 'STARDUST DRUG & MEDICAL SUPPLIES CORP.', 'trade_name' => 'STARDUST DRUG & MEDICAL SUPPLIES CORP.', 'identification_number' => '005-588-721-00000', 'address' => '1642-1648 RIZAL AVE. BRY. 339, ZONE 34, 1014 STA. CRUZ CITY OF MANILA'],
            ['name' => 'ST. RAPHAEL LIFELINE CO.', 'trade_name' => 'ST. RAPHAEL LIFELINE CO.', 'identification_number' => '208-226-799-000', 'address' => '2F LANDMARK BRGY. MC ATHUR HWAY KALAYAAN VILL QUEBIAWAN CSFP'],
            ['name' => 'TROIKAA PHARMA. PHILS. INC.', 'trade_name' => 'TROIKAA PHARMA. PHILS. INC.', 'identification_number' => '009-182-253-00000', 'address' => 'UNIT 505 RICHMONDE PLAZA PEARL DRIVE DRIVE COR. LOURDES ST. ORTIGAS CTR. SAN ANTONIO CITY OF PASIG'],
            ['name' => 'VITALIFE PHARMA & MEDICAL SUPPLY INC.', 'trade_name' => 'VITALIFE PHARMA & MEDICAL SUPPLY INC.', 'identification_number' => '007-098-911-001', 'address' => '2ND FL LEMON SQUARE BLDG. 1199 EDSA  BRGY. KATIPUNAN DIST. 1 AREA 2,  Q.C.'],
            ['name' => 'ZUELLIG PHARMA CORPORATION', 'trade_name' => 'ZUELLIG PHARMA CORPORATION', 'identification_number' => '000-172-443-000', 'address' => 'KM 18 WEST SERVICE ROAD SOUTH SUPER HIGHWAY COR. EDISON AVENUE, BRGY. SAN VALLEY, PARANAQUE, Q.C.'],
        ];

        DB::table('suppliers')->insert($suppliers);
    }
}
