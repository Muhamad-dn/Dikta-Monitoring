<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\DeviceSpecification;
use App\Models\DeviceHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeviceSpecificationAndStockSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('role', 'admin')->first();
        $actorId = $admin ? $admin->id : 1;

        $specs = array (
  0 => 
  array (
    'spec_code' => 'B2WS0200138M',
    'name' => 'Adapter 12V, 1.5A For Bivocom',
    'brand' => 'Bivocom',
    'type' => 'Power Supply',
  ),
  1 => 
  array (
    'spec_code' => 'B2WSBKA0138M',
    'name' => 'Bivocom Industrial 4G Router 5 Ports ,Dual Sim, Wifi, Dual Modul',
    'brand' => 'Bivocom',
    'type' => 'Router',
  ),
  2 => 
  array (
    'spec_code' => 'B2WS02A70202',
    'name' => 'Psu For Evolution X3 Satelite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Power Supply',
  ),
  3 => 
  array (
    'spec_code' => 'B2WS02A70201',
    'name' => 'Evolution X3 Satellite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Router',
  ),
  4 => 
  array (
    'spec_code' => 'B2WN0150048M',
    'name' => 'Ac Power Supply For Rax 711 Series',
    'brand' => 'Raisecom',
    'type' => 'Power Supply',
  ),
  5 => 
  array (
    'spec_code' => 'B2WN0150049M',
    'name' => 'Dc Power Supply For Rax 711 Series',
    'brand' => 'Raisecom',
    'type' => 'Power Supply',
  ),
  6 => 
  array (
    'spec_code' => 'B2WS03VI0103',
    'name' => 'Dro Lnb C-Band For Idirect Remote ( Pn - 8220 )',
    'brand' => 'iDirect',
    'type' => 'LNB/LNA',
  ),
  7 => 
  array (
    'spec_code' => 'B2WS0100010I',
    'name' => 'Lnb,C-Band Ls Extd Band ( Pn - 1024573-0001 )',
    'brand' => 'PT DIKTA',
    'type' => 'LNB/LNA',
  ),
  8 => 
  array (
    'spec_code' => 'B2WS0200023M',
    'name' => 'X1 Satellite Router 90W Ku/C',
    'brand' => 'PT DIKTA',
    'type' => 'Router',
  ),
  9 => 
  array (
    'spec_code' => 'B2WS02A70204',
    'name' => 'Psu For Evolution X1 Satelite Router',
    'brand' => 'iDirect',
    'type' => 'Power Supply',
  ),
  10 => 
  array (
    'spec_code' => 'B2WS02A70203',
    'name' => 'Evolution X1 Satellite Router',
    'brand' => 'iDirect',
    'type' => 'Router',
  ),
  11 => 
  array (
    'spec_code' => 'B2WS02VU010G',
    'name' => 'Wibas Connect Poe Injector',
    'brand' => 'Intracom',
    'type' => 'PoE Injector',
  ),
  12 => 
  array (
    'spec_code' => 'B2WS02VU010E',
    'name' => 'Wibas Connect Fully Outdoor Terminal Station Cpe 10.5 Ghz Hi',
    'brand' => 'Intracom',
    'type' => 'Radio',
  ),
  13 => 
  array (
    'spec_code' => 'B2WS02VU010F',
    'name' => 'Wibas Connect Panel Antenna 10.5 Ghz',
    'brand' => 'Intracom',
    'type' => 'Antenna',
  ),
  14 => 
  array (
    'spec_code' => 'B2WS030O0105',
    'name' => 'Rf,Odu,Tigris,Ext-C 2W',
    'brand' => 'Tigris',
    'type' => 'BUC/RFU',
  ),
  15 => 
  array (
    'spec_code' => 'B2WS010Q010T',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  16 => 
  array (
    'spec_code' => 'B2WS010Q0102',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  17 => 
  array (
    'spec_code' => 'B2WS03VI0102',
    'name' => '2W C-Band Buc For Idirect Remote ( Pn - Njt5667F )',
    'brand' => 'iDirect',
    'type' => 'BUC/RFU',
  ),
  18 => 
  array (
    'spec_code' => 'B2WS0200037M',
    'name' => 'Full C-Band 2W Buc (Njt8102Wf)',
    'brand' => 'NJRC',
    'type' => 'BUC/RFU',
  ),
  19 => 
  array (
    'spec_code' => 'B2WS030O0107',
    'name' => 'Rfu-Excb- Tig-2Wl-F-C-Band',
    'brand' => 'PT DIKTA',
    'type' => 'BUC/RFU',
  ),
  20 => 
  array (
    'spec_code' => 'B2WN0286916M',
    'name' => 'VMware SD-WAN Edge 510 Appliance, Deployment: Software Orchestrator for 1 year, 3 Year or 5 Year (4x1G RJ45 Port, Integrated Wif',
    'brand' => 'PT DIKTA',
    'type' => 'Lainnya',
  ),
  21 => 
  array (
    'spec_code' => 'B2WN01000462',
    'name' => 'Ont Iscom Ht-803G-07 ( Pn - )',
    'brand' => 'Raisecom',
    'type' => 'ONT',
  ),
  22 => 
  array (
    'spec_code' => 'B2WN0150153M',
    'name' => 'Adaptor 12V 1A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  23 => 
  array (
    'spec_code' => 'B2WN0286496M',
    'name' => 'Fortigate 60F',
    'brand' => 'Fortinet',
    'type' => 'Lainnya',
  ),
  24 => 
  array (
    'spec_code' => 'B2WN0286499M',
    'name' => 'Ac Power Adaptor For Fg/Fwf 60E/61E, 60F/61F, And 80E/81E',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  25 => 
  array (
    'spec_code' => 'B2WN0286773M',
    'name' => 'Optical Network Terminal ( Ont ) Iscom5104G-Gp-Hb-07',
    'brand' => 'Raisecom',
    'type' => 'ONT',
  ),
  26 => 
  array (
    'spec_code' => 'B2WN0286873M',
    'name' => 'Power Supply o/p 24VDC - 1,2A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  27 => 
  array (
    'spec_code' => 'B2WS028N0113',
    'name' => 'Mikrotik Rb951Ui-2Hnd ( Pn - ) ( Pn - )',
    'brand' => 'MikroTik',
    'type' => 'Lainnya',
  ),
  28 => 
  array (
    'spec_code' => 'B2WN0286872M',
    'name' => 'Router Mikrotik Rb760Igs',
    'brand' => 'MikroTik',
    'type' => 'Router',
  ),
  29 => 
  array (
    'spec_code' => 'B2WS0138115M',
    'name' => 'Radio Mimosa C5x',
    'brand' => 'Mimosa',
    'type' => 'Radio',
  ),
  30 => 
  array (
    'spec_code' => 'B2WS0138118M',
    'name' => 'Mimosa PoE Wall Plug 56V Eu',
    'brand' => 'Mimosa',
    'type' => 'PoE Injector',
  ),
  31 => 
  array (
    'spec_code' => 'B2WS0391069M',
    'name' => 'PoE B5c Connectorized PTP',
    'brand' => 'PT DIKTA',
    'type' => 'PoE Injector',
  ),
  32 => 
  array (
    'spec_code' => 'B2WS0391071M',
    'name' => 'Mimosa B5c Connectorized PTP',
    'brand' => 'Mimosa',
    'type' => 'Lainnya',
  ),
  33 => 
  array (
    'spec_code' => 'B2WS020O0110',
    'name' => 'Psu Remote 7000 Series -Idu- C-Band Hughes ( Pn - 1031105-0001 )',
    'brand' => 'Hughes',
    'type' => 'Power Supply',
  ),
  34 => 
  array (
    'spec_code' => 'USTLWSAJ0102',
    'name' => 'Uso-Remote Hx50-Idu- C-Band Hughes',
    'brand' => 'Hughes',
    'type' => 'Satellite Remote',
  ),
  35 => 
  array (
    'spec_code' => 'B2WS0138158M',
    'name' => 'Remote Enterprise Flat Hp-Starlink',
    'brand' => 'Starlink',
    'type' => 'Satellite Remote',
  ),
  36 => 
  array (
    'spec_code' => 'B2WN0286417M',
    'name' => 'Psu 12V, 3A For Fortigate 40 F',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  37 => 
  array (
    'spec_code' => 'B2WN0286418M',
    'name' => 'Fortigate 40F',
    'brand' => 'Fortinet',
    'type' => 'Lainnya',
  ),
  38 => 
  array (
    'spec_code' => 'B1AC29A0029M',
    'name' => 'AC Power Adapter 65W FOR ThinkEdge SE30 Edge Client',
    'brand' => 'Lenovo',
    'type' => 'Power Supply',
  ),
  39 => 
  array (
    'spec_code' => 'B2WS05A0087M',
    'name' => 'TL-POE160S PoE+ Injector',
    'brand' => 'TP-Link',
    'type' => 'PoE Injector',
  ),
  40 => 
  array (
    'spec_code' => 'B2WN13A0059M',
    'name' => 'Meraki Mr44 Wifi 6 Indoor Ap',
    'brand' => 'Cisco',
    'type' => 'Lainnya',
  ),
  41 => 
  array (
    'spec_code' => 'B2WN0150266M',
    'name' => 'L2+ managed POE switch with 24GE + 4SFP+, 24port POE with max 370W limit and smart',
    'brand' => 'PT DIKTA',
    'type' => 'PoE Injector',
  ),
  42 => 
  array (
    'spec_code' => 'B2WN0286894M',
    'name' => 'Versa Certified Hardware V110, Versa 2C(V100) Rcc-Ve2, C2558 Cpu, 4Gb Ddr, Ecc, 32Gb Flash',
    'brand' => 'Versa',
    'type' => 'Lainnya',
  ),
  43 => 
  array (
    'spec_code' => 'B2WN0286290M',
    'name' => 'Mikrotik Ltap Mini Lte Kit - Rb912R-2Nd-Ltm&R11E-Lte',
    'brand' => 'MikroTik',
    'type' => 'Lainnya',
  ),
  44 => 
  array (
    'spec_code' => 'B2WN0100041X',
    'name' => '3Rd Party Gpon Ont With 4Ge + 2 Pots ( Pn - 178-80003 )',
    'brand' => 'PT DIKTA',
    'type' => 'ONT',
  ),
  45 => 
  array (
    'spec_code' => 'B2WN01000440',
    'name' => 'Ont 4Ge + 2 Post + Wifi (3Rd Prty Cig) ( Pn - )',
    'brand' => 'PT DIKTA',
    'type' => 'ONT',
  ),
  46 => 
  array (
    'spec_code' => 'B2WS03440102',
    'name' => 'PROXIM POE GIGA BIT',
    'brand' => 'Proxim',
    'type' => 'PoE Injector',
  ),
  47 => 
  array (
    'spec_code' => 'B2WN0262020G',
    'name' => 'Fortigate-30E Hw And Warranty',
    'brand' => 'Fortinet',
    'type' => 'Lainnya',
  ),
  48 => 
  array (
    'spec_code' => 'B2WN0262020H',
    'name' => 'Fortigate-50E Hw And Warranty',
    'brand' => 'Fortinet',
    'type' => 'Lainnya',
  ),
  49 => 
  array (
    'spec_code' => 'B2WN00A0030M',
    'name' => 'AC Power Adaptor for FG/FWF-40F Project Lambda',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  50 => 
  array (
    'spec_code' => 'B2WN0286208M',
    'name' => 'Psu Forti Fg-50E/Fg-30E',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  51 => 
  array (
    'spec_code' => 'B2WN0202011N',
    'name' => 'Next Generation Carrier Ethernet Demarcation Device',
    'brand' => 'PT DIKTA',
    'type' => 'Interface Module',
  ),
  52 => 
  array (
    'spec_code' => 'B2WS0391020H',
    'name' => 'Ac Poe Device Power Radwin ( Pn - 9921W1011 )',
    'brand' => 'Radwin',
    'type' => 'PoE Injector',
  ),
  53 => 
  array (
    'spec_code' => 'B4CP0113433M',
    'name' => 'Poe Injector Ap 720 Rujije Tambahan 2023 (Rg-Poe-Af15)',
    'brand' => 'PT DIKTA',
    'type' => 'PoE Injector',
  ),
  54 => 
  array (
    'spec_code' => 'B2WN00A0363M',
    'name' => 'Transceiver BIDI SFP 1.25G Tx1310nm 20km DDM',
    'brand' => 'PT DIKTA',
    'type' => 'SFP Transceiver',
  ),
  55 => 
  array (
    'spec_code' => 'B2WN00A0367M',
    'name' => 'Transceiver BIDI SFP 1.25G Tx1550nm 20km DDM',
    'brand' => 'PT DIKTA',
    'type' => 'SFP Transceiver',
  ),
  56 => 
  array (
    'spec_code' => 'B2WN0231016U',
    'name' => '1-PORT SERIAL WAN INTERFACE CARD',
    'brand' => 'PT DIKTA',
    'type' => 'Interface Module',
  ),
  57 => 
  array (
    'spec_code' => 'B2WN0231010C',
    'name' => '2 PORT SERIAL WAN INTERFACE CARD FOR CISCO 2600',
    'brand' => 'Cisco',
    'type' => 'Interface Module',
  ),
  58 => 
  array (
    'spec_code' => 'B2WS0391052M',
    'name' => 'Cambium Force-200, 5 Ghz, High Gain 25 Dbi Antenna, Up To 200+Mbps Throughput',
    'brand' => 'Cambium Networks',
    'type' => 'Antenna',
  ),
  59 => 
  array (
    'spec_code' => 'B2WS0391048M',
    'name' => 'Poe Cambium Force 200',
    'brand' => 'Cambium Networks',
    'type' => 'PoE Injector',
  ),
  60 => 
  array (
    'spec_code' => 'B2WN0177010F',
    'name' => 'Fiber Driver Eth Raisecom Rc552-Fe-Ss13 ( Pn - )',
    'brand' => 'Raisecom',
    'type' => 'Lainnya',
  ),
  61 => 
  array (
    'spec_code' => 'B2WN01AH010H',
    'name' => 'Raisecom Module Ge With 1:1 Uplink Protection 2 Port Sfp',
    'brand' => 'Raisecom',
    'type' => 'SFP Transceiver',
  ),
  62 => 
  array (
    'spec_code' => 'B2WN0287084M',
    'name' => 'Intelligent Ethernet Demarcation Device/Router consisting Compact 10G NID, half-19\'\' wide,',
    'brand' => 'PT DIKTA',
    'type' => 'Router',
  ),
  63 => 
  array (
    'spec_code' => 'B2WN0286405M',
    'name' => 'Ac Power Supply For Rax711-C-R Series',
    'brand' => 'Raisecom',
    'type' => 'Power Supply',
  ),
  64 => 
  array (
    'spec_code' => 'B2WS0391128M',
    'name' => 'NJRC LNA C-BAND SCPC',
    'brand' => 'NJRC',
    'type' => 'LNB/LNA',
  ),
  65 => 
  array (
    'spec_code' => 'B2WS0200122M',
    'name' => 'Remote Intracom 10,5 ( Wibas Connect ) Wibas Terminal Station, 10,5Ghz , 1Xgbf',
    'brand' => 'Intracom',
    'type' => 'Radio',
  ),
  66 => 
  array (
    'spec_code' => 'B2WS0100010H',
    'name' => 'Lna Vsat Link',
    'brand' => 'PT DIKTA',
    'type' => 'LNB/LNA',
  ),
  67 => 
  array (
    'spec_code' => 'B2WS033A0103',
    'name' => 'Odu-Sp 13 Ghz Low Nera ( Pn - )',
    'brand' => 'PT DIKTA',
    'type' => 'Lainnya',
  ),
  68 => 
  array (
    'spec_code' => 'B2WN01310468',
    'name' => 'TWO-PORT VOICE INTERFACE CARD- FXS AND DID',
    'brand' => 'PT DIKTA',
    'type' => 'Lainnya',
  ),
  69 => 
  array (
    'spec_code' => 'B2WN0231017C',
    'name' => '4 PORT VIC3-4FXS DAN DID',
    'brand' => 'PT DIKTA',
    'type' => 'Interface Module',
  ),
  70 => 
  array (
    'spec_code' => 'B2WS0391204M',
    'name' => 'Cambium Force-300L, 5 Ghz, High Gain 25 Dbi Antenna, Up To 400+Mbps Throughput',
    'brand' => 'Cambium Networks',
    'type' => 'Antenna',
  ),
  71 => 
  array (
    'spec_code' => 'B2WN01310478',
    'name' => 'Cisco 4321+Waas',
    'brand' => 'Cisco',
    'type' => 'Lainnya',
  ),
  72 => 
  array (
    'spec_code' => 'B2WS020O0116',
    'name' => 'Remote Hx50-Idu- C-Band Hughes',
    'brand' => 'Hughes',
    'type' => 'Satellite Remote',
  ),
  73 => 
  array (
    'spec_code' => 'B2WN0286830M',
    'name' => 'Tabletop, With Fan Nca-1515B-4C-8D-64S',
    'brand' => 'PT DIKTA',
    'type' => 'Lainnya',
  ),
  74 => 
  array (
    'spec_code' => 'B2WN0286829M',
    'name' => 'Power Adapter Nca-1515B 12V=5A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  75 => 
  array (
    'spec_code' => 'B2WN0150107M',
    'name' => 'Ac Power Supply For Cisco Isr 4320',
    'brand' => 'Cisco',
    'type' => 'Power Supply',
  ),
  76 => 
  array (
    'spec_code' => 'B2WN0131043M',
    'name' => 'Cisco 2921,Sre 700,Waas Eterprise Medium And Sec',
    'brand' => 'Cisco',
    'type' => 'Lainnya',
  ),
  77 => 
  array (
    'spec_code' => 'B2WN01AH0204',
    'name' => 'Raisecom 1U, 1-Slot Chassis, With One Ac Power Supply Connector ( Pn - Rc001-1Ac )',
    'brand' => 'Raisecom',
    'type' => 'Power Supply',
  ),
  78 => 
  array (
    'spec_code' => 'B2WN0287010M',
    'name' => 'Versa Ready 4 Core 8Gb Ram 16G Ammc',
    'brand' => 'Versa',
    'type' => 'Lainnya',
  ),
  79 => 
  array (
    'spec_code' => 'B3CD115R0105',
    'name' => 'Isolation Transformer (It) 600Va ( Pn - )',
    'brand' => 'PT DIKTA',
    'type' => 'Lainnya',
  ),
  80 => 
  array (
    'spec_code' => 'B2WN00A0392M',
    'name' => '4 x GE RJ45 ports (including 3 x Internal Ports, 1 x WAN Ports)',
    'brand' => 'PT DIKTA',
    'type' => 'Lainnya',
  ),
  81 => 
  array (
    'spec_code' => 'B2WN00A0391M',
    'name' => 'AC power adaptors for FG/FWF-30G, come with interchangeable power plugs',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  82 => 
  array (
    'spec_code' => 'B2WS0138161M',
    'name' => 'Standard V4',
    'brand' => 'PT DIKTA',
    'type' => 'Lainnya',
  ),
  83 => 
  array (
    'spec_code' => 'B2WS0138162M',
    'name' => 'Router Starlink V4',
    'brand' => 'Starlink',
    'type' => 'Router',
  ),
  84 => 
  array (
    'spec_code' => 'B2WS00A0013M',
    'name' => 'Power Supply Starlink V4',
    'brand' => 'Starlink',
    'type' => 'Power Supply',
  ),
  85 => 
  array (
    'spec_code' => 'B2WN0287000M',
    'name' => 'Ethernet Switch Demarcation Device, Consisting 2Xge Optical (Sfp) Nni, 4Xge Electrical (Rj45)',
    'brand' => 'PT DIKTA',
    'type' => 'Switch',
  ),
  86 => 
  array (
    'spec_code' => 'B2WN0287082M',
    'name' => 'AC Power Supply for Cisco ISR 4320 series Project Lambda',
    'brand' => 'Cisco',
    'type' => 'Power Supply',
  ),
  87 => 
  array (
    'spec_code' => 'B2WN0287081M',
    'name' => 'Cisco ISR 4321 (2GE,2NIM,4G FLASH,4G DRAM,IPB)',
    'brand' => 'Cisco',
    'type' => 'Interface Module',
  ),
  88 => 
  array (
    'spec_code' => 'B2WN00A0092M',
    'name' => '2-port GE WAN NIM, dual-mode RJ45 & SFP',
    'brand' => 'PT DIKTA',
    'type' => 'SFP Transceiver',
  ),
  89 => 
  array (
    'spec_code' => 'B2WS02VU0104',
    'name' => 'Panel Antenna 10.5Ghz Wibas Terminal Station',
    'brand' => 'Intracom',
    'type' => 'Antenna',
  ),
  90 => 
  array (
    'spec_code' => 'B2WS02VU0103',
    'name' => 'Full Outdoor Wibas Terminal Station, 10.5Ghz Fdd, 2Xgbe ( Pn - )',
    'brand' => 'Intracom',
    'type' => 'Radio',
  ),
  91 => 
  array (
    'spec_code' => 'B2WS020O011X',
    'name' => 'Buc Full Cband Rf-Cb-2W-Jptr ( Pn - Rf-Cb-2W-Jptr )',
    'brand' => 'PT DIKTA',
    'type' => 'BUC/RFU',
  ),
  92 => 
  array (
    'spec_code' => 'B2WS02VU0105',
    'name' => 'Psu/Poe Wibas',
    'brand' => 'Intracom',
    'type' => 'Power Supply',
  ),
  93 => 
  array (
    'spec_code' => 'B2WN0100041P',
    'name' => 'Enconn Eco Power 130 T Poe Over Coax Transmitter ( Pn - Eco-Power130T )',
    'brand' => 'Enconn',
    'type' => 'PoE Injector',
  ),
  94 => 
  array (
    'spec_code' => 'B2WN0100041Q',
    'name' => 'Enconn Eco Power 130 R Poe Over Coax Receiver ( Pn - ) ( Pn - Eco-Power130R )',
    'brand' => 'Enconn',
    'type' => 'PoE Injector',
  ),
  95 => 
  array (
    'spec_code' => 'B2WSBKA0139M',
    'name' => 'Adapter 12V, 1.5A For Bivocom',
    'brand' => 'Bivocom',
    'type' => 'Power Supply',
  ),
  96 => 
  array (
    'spec_code' => 'B2WS0200137M',
    'name' => 'Bivocom Industrial 4G Router 5 Ports, Dual Sim, Wifi, Dual Modul',
    'brand' => 'Bivocom',
    'type' => 'Router',
  ),
  97 => 
  array (
    'spec_code' => 'B2WS01AA0102',
    'name' => 'Antenna Asc Signal (Raven) 1.8 M',
    'brand' => 'ASC Signal',
    'type' => 'Antenna',
  ),
  98 => 
  array (
    'spec_code' => 'B2WS010Q0101',
    'name' => 'Antenna/Reflector 1.8 Prodelin',
    'brand' => 'Prodelin',
    'type' => 'Antenna',
  ),
  99 => 
  array (
    'spec_code' => 'B2WS01VE0105',
    'name' => 'Antenna 1,8 Skyware',
    'brand' => 'Skyware',
    'type' => 'Antenna',
  ),
  100 => 
  array (
    'spec_code' => 'B2WS01000103',
    'name' => 'Canister 1.8 Diameter 4 Inchi',
    'brand' => 'PT DIKTA',
    'type' => 'Canister',
  ),
  101 => 
  array (
    'spec_code' => 'B2WS0100012O',
    'name' => 'Canister Skyware 5 Inch ( Pn - )',
    'brand' => 'Skyware',
    'type' => 'Canister',
  ),
);

        foreach ($specs as $spec) {
            DeviceSpecification::updateOrCreate(
                ['spec_code' => $spec['spec_code']],
                [
                    'name' => $spec['name'],
                    'brand' => $spec['brand'],
                    'type' => $spec['type'],
                ]
            );
        }

        $devices = array (
  0 => 
  array (
    'kode_spek' => 'B2WS0200138M',
    'registration_number' => 'B2WS0200138MA0890B',
    'serial_number' => 'B2WS0200138MA0890B',
    'name' => 'Adapter 12V, 1.5A For Bivocom',
    'brand' => 'Bivocom',
    'type' => 'Power Supply',
  ),
  1 => 
  array (
    'kode_spek' => 'B2WSBKA0138M',
    'registration_number' => 'B2WSBKA0138MA0323',
    'serial_number' => 'B2WSBKA0138MA0323',
    'name' => 'Bivocom Industrial 4G Router 5 Ports ,Dual Sim, Wifi, Dual Modul',
    'brand' => 'Bivocom',
    'type' => 'Router',
  ),
  2 => 
  array (
    'kode_spek' => 'B2WS02A70202',
    'registration_number' => 'B2WS02A7020202209',
    'serial_number' => 'B2WS02A7020202209',
    'name' => 'Psu For Evolution X3 Satelite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Power Supply',
  ),
  3 => 
  array (
    'kode_spek' => 'B2WS02A70201',
    'registration_number' => 'B2WS02A7020100309',
    'serial_number' => 'B2WS02A7020100309',
    'name' => 'Evolution X3 Satellite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Router',
  ),
  4 => 
  array (
    'kode_spek' => 'B2WS0200138M',
    'registration_number' => 'B2WS0200138MA1179B',
    'serial_number' => 'B2WS0200138MA1179B',
    'name' => 'Adapter 12V, 1.5A For Bivocom',
    'brand' => 'Bivocom',
    'type' => 'Power Supply',
  ),
  5 => 
  array (
    'kode_spek' => 'B2WN0150048M',
    'registration_number' => 'B2WN0150048MA0308B',
    'serial_number' => 'B2WN0150048MA0308B',
    'name' => 'Ac Power Supply For Rax 711 Series',
    'brand' => 'Raisecom',
    'type' => 'Power Supply',
  ),
  6 => 
  array (
    'kode_spek' => 'B2WN0150049M',
    'registration_number' => 'B2WN0150049MA0362B',
    'serial_number' => 'B2WN0150049MA0362B',
    'name' => 'Dc Power Supply For Rax 711 Series',
    'brand' => 'Raisecom',
    'type' => 'Power Supply',
  ),
  7 => 
  array (
    'kode_spek' => 'B2WS03VI0103',
    'registration_number' => 'B2WS03VI010302167',
    'serial_number' => 'B2WS03VI010302167',
    'name' => 'Dro Lnb C-Band For Idirect Remote ( Pn - 8220 )',
    'brand' => 'iDirect',
    'type' => 'LNB/LNA',
  ),
  8 => 
  array (
    'kode_spek' => 'B2WS0100010I',
    'registration_number' => 'B2WS0100010I02675',
    'serial_number' => 'B2WS0100010I02675',
    'name' => 'Lnb,C-Band Ls Extd Band ( Pn - 1024573-0001 )',
    'brand' => 'PT DIKTA',
    'type' => 'LNB/LNA',
  ),
  9 => 
  array (
    'kode_spek' => 'B2WS0200023M',
    'registration_number' => 'B2WS0200023MA0343',
    'serial_number' => 'B2WS0200023MA0343',
    'name' => 'X1 Satellite Router 90W Ku/C',
    'brand' => 'PT DIKTA',
    'type' => 'Router',
  ),
  10 => 
  array (
    'kode_spek' => 'B2WS0100010I',
    'registration_number' => 'B2WS0100010I07702',
    'serial_number' => 'B2WS0100010I07702',
    'name' => 'Lnb,C-Band Ls Extd Band ( Pn - 1024573-0001 )',
    'brand' => 'PT DIKTA',
    'type' => 'LNB/LNA',
  ),
  11 => 
  array (
    'kode_spek' => 'B2WS02A70204',
    'registration_number' => 'B2WS02A7020400725',
    'serial_number' => 'B2WS02A7020400725',
    'name' => 'Psu For Evolution X1 Satelite Router',
    'brand' => 'iDirect',
    'type' => 'Power Supply',
  ),
  12 => 
  array (
    'kode_spek' => 'B2WS03VI0103',
    'registration_number' => 'B2WS03VI010300693',
    'serial_number' => 'B2WS03VI010300693',
    'name' => 'Dro Lnb C-Band For Idirect Remote ( Pn - 8220 )',
    'brand' => 'iDirect',
    'type' => 'LNB/LNA',
  ),
  13 => 
  array (
    'kode_spek' => 'B2WS02A70203',
    'registration_number' => 'B2WS02A7020303736',
    'serial_number' => 'B2WS02A7020303736',
    'name' => 'Evolution X1 Satellite Router',
    'brand' => 'iDirect',
    'type' => 'Router',
  ),
  14 => 
  array (
    'kode_spek' => 'B2WS02VU010G',
    'registration_number' => 'B2WS02VU010GA0581',
    'serial_number' => 'B2WS02VU010GA0581',
    'name' => 'Wibas Connect Poe Injector',
    'brand' => 'Intracom',
    'type' => 'PoE Injector',
  ),
  15 => 
  array (
    'kode_spek' => 'B2WS02VU010G',
    'registration_number' => 'B2WS02VU010GA0703',
    'serial_number' => 'B2WS02VU010GA0703',
    'name' => 'Wibas Connect Poe Injector',
    'brand' => 'Intracom',
    'type' => 'PoE Injector',
  ),
  16 => 
  array (
    'kode_spek' => 'B2WS02VU010E',
    'registration_number' => 'B2WS02VU010E00341',
    'serial_number' => 'B2WS02VU010E00341',
    'name' => 'Wibas Connect Fully Outdoor Terminal Station Cpe 10.5 Ghz Hi',
    'brand' => 'Intracom',
    'type' => 'Radio',
  ),
  17 => 
  array (
    'kode_spek' => 'B2WS02VU010F',
    'registration_number' => 'B2WS02VU010F00040',
    'serial_number' => 'B2WS02VU010F00040',
    'name' => 'Wibas Connect Panel Antenna 10.5 Ghz',
    'brand' => 'Intracom',
    'type' => 'Antenna',
  ),
  18 => 
  array (
    'kode_spek' => 'B2WS0100010I',
    'registration_number' => 'B2WS0100010I10896',
    'serial_number' => 'B2WS0100010I10896',
    'name' => 'Lnb,C-Band Ls Extd Band ( Pn - 1024573-0001 )',
    'brand' => 'PT DIKTA',
    'type' => 'LNB/LNA',
  ),
  19 => 
  array (
    'kode_spek' => 'B2WS030O0105',
    'registration_number' => 'B2WS030O010502456',
    'serial_number' => 'B2WS030O010502456',
    'name' => 'Rf,Odu,Tigris,Ext-C 2W',
    'brand' => 'Tigris',
    'type' => 'BUC/RFU',
  ),
  20 => 
  array (
    'kode_spek' => 'B2WS010Q010T',
    'registration_number' => 'B2WS010Q010T00741',
    'serial_number' => 'B2WS010Q010T00741',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  21 => 
  array (
    'kode_spek' => 'B2WS010Q0102',
    'registration_number' => 'B2WS010Q010205250',
    'serial_number' => 'B2WS010Q010205250',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  22 => 
  array (
    'kode_spek' => 'B2WS010Q010T',
    'registration_number' => 'B2WS010Q010T04811',
    'serial_number' => 'B2WS010Q010T04811',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  23 => 
  array (
    'kode_spek' => 'B2WS03VI0103',
    'registration_number' => 'B2WS03VI010302727',
    'serial_number' => 'B2WS03VI010302727',
    'name' => 'Dro Lnb C-Band For Idirect Remote ( Pn - 8220 )',
    'brand' => 'iDirect',
    'type' => 'LNB/LNA',
  ),
  24 => 
  array (
    'kode_spek' => 'B2WS03VI0102',
    'registration_number' => 'B2WS03VI010205407',
    'serial_number' => 'B2WS03VI010205407',
    'name' => '2W C-Band Buc For Idirect Remote ( Pn - Njt5667F )',
    'brand' => 'iDirect',
    'type' => 'BUC/RFU',
  ),
  25 => 
  array (
    'kode_spek' => 'B2WS010Q0102',
    'registration_number' => 'B2WS010Q0102A0253',
    'serial_number' => 'B2WS010Q0102A0253',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  26 => 
  array (
    'kode_spek' => 'B2WS03VI0103',
    'registration_number' => 'B2WS03VI010305434',
    'serial_number' => 'B2WS03VI010305434',
    'name' => 'Dro Lnb C-Band For Idirect Remote ( Pn - 8220 )',
    'brand' => 'iDirect',
    'type' => 'LNB/LNA',
  ),
  27 => 
  array (
    'kode_spek' => 'B2WS0200037M',
    'registration_number' => 'B2WS0200037MA0459',
    'serial_number' => 'B2WS0200037MA0459',
    'name' => 'Full C-Band 2W Buc (Njt8102Wf)',
    'brand' => 'NJRC',
    'type' => 'BUC/RFU',
  ),
  28 => 
  array (
    'kode_spek' => 'B2WS010Q010T',
    'registration_number' => 'B2WS010Q010T01066',
    'serial_number' => 'B2WS010Q010T01066',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  29 => 
  array (
    'kode_spek' => 'B2WS03VI0103',
    'registration_number' => 'B2WS03VI010302251',
    'serial_number' => 'B2WS03VI010302251',
    'name' => 'Dro Lnb C-Band For Idirect Remote ( Pn - 8220 )',
    'brand' => 'iDirect',
    'type' => 'LNB/LNA',
  ),
  30 => 
  array (
    'kode_spek' => 'B2WS03VI0102',
    'registration_number' => 'B2WS03VI010206088',
    'serial_number' => 'B2WS03VI010206088',
    'name' => '2W C-Band Buc For Idirect Remote ( Pn - Njt5667F )',
    'brand' => 'iDirect',
    'type' => 'BUC/RFU',
  ),
  31 => 
  array (
    'kode_spek' => 'B2WS030O0107',
    'registration_number' => 'B2WS030O010708248',
    'serial_number' => 'B2WS030O010708248',
    'name' => 'Rfu-Excb- Tig-2Wl-F-C-Band',
    'brand' => 'PT DIKTA',
    'type' => 'BUC/RFU',
  ),
  32 => 
  array (
    'kode_spek' => 'B2WS010Q010T',
    'registration_number' => 'B2WS010Q010T00909',
    'serial_number' => 'B2WS010Q010T00909',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  33 => 
  array (
    'kode_spek' => 'B2WS0100010I',
    'registration_number' => 'B2WS0100010IA0374',
    'serial_number' => 'B2WS0100010IA0374',
    'name' => 'Lnb,C-Band Ls Extd Band ( Pn - 1024573-0001 )',
    'brand' => 'PT DIKTA',
    'type' => 'LNB/LNA',
  ),
  34 => 
  array (
    'kode_spek' => 'B2WS03VI0102',
    'registration_number' => 'B2WS03VI010207059',
    'serial_number' => 'B2WS03VI010207059',
    'name' => '2W C-Band Buc For Idirect Remote ( Pn - Njt5667F )',
    'brand' => 'iDirect',
    'type' => 'BUC/RFU',
  ),
  35 => 
  array (
    'kode_spek' => 'B2WS02A70203',
    'registration_number' => 'B2WS02A7020302416',
    'serial_number' => 'B2WS02A7020302416',
    'name' => 'Evolution X1 Satellite Router',
    'brand' => 'iDirect',
    'type' => 'Router',
  ),
  36 => 
  array (
    'kode_spek' => 'B2WS02A70203',
    'registration_number' => 'B2WS02A7020303442',
    'serial_number' => 'B2WS02A7020303442',
    'name' => 'Evolution X1 Satellite Router',
    'brand' => 'iDirect',
    'type' => 'Router',
  ),
  37 => 
  array (
    'kode_spek' => 'B2WS010Q010T',
    'registration_number' => 'B2WS010Q010T05983',
    'serial_number' => 'B2WS010Q010T05983',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  38 => 
  array (
    'kode_spek' => 'B2WS03VI0103',
    'registration_number' => 'B2WS03VI010303373',
    'serial_number' => 'B2WS03VI010303373',
    'name' => 'Dro Lnb C-Band For Idirect Remote ( Pn - 8220 )',
    'brand' => 'iDirect',
    'type' => 'LNB/LNA',
  ),
  39 => 
  array (
    'kode_spek' => 'B2WS010Q010T',
    'registration_number' => 'B2WS010Q010T05991',
    'serial_number' => 'B2WS010Q010T05991',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  40 => 
  array (
    'kode_spek' => 'B2WS03VI0103',
    'registration_number' => 'B2WS03VI010300010',
    'serial_number' => 'B2WS03VI010300010',
    'name' => 'Dro Lnb C-Band For Idirect Remote ( Pn - 8220 )',
    'brand' => 'iDirect',
    'type' => 'LNB/LNA',
  ),
  41 => 
  array (
    'kode_spek' => 'B2WS0200037M',
    'registration_number' => 'B2WS0200037MA0865',
    'serial_number' => 'B2WS0200037MA0865',
    'name' => 'Full C-Band 2W Buc (Njt8102Wf)',
    'brand' => 'NJRC',
    'type' => 'BUC/RFU',
  ),
  42 => 
  array (
    'kode_spek' => 'B2WS0200037M',
    'registration_number' => 'B2WS0200037MA0150',
    'serial_number' => 'B2WS0200037MA0150',
    'name' => 'Full C-Band 2W Buc (Njt8102Wf)',
    'brand' => 'NJRC',
    'type' => 'BUC/RFU',
  ),
  43 => 
  array (
    'kode_spek' => 'B2WN0286916M',
    'registration_number' => 'B2WN0286916MA0089',
    'serial_number' => 'B2WN0286916MA0089',
    'name' => 'VMware SD-WAN Edge 510 Appliance, Deployment: Software Orchestrator for 1 year, 3 Year or 5 Year (4x1G RJ45 Port, Integrated Wif',
    'brand' => 'PT DIKTA',
    'type' => 'Lainnya',
  ),
  44 => 
  array (
    'kode_spek' => 'B2WN01000462',
    'registration_number' => 'B2WN01000462A3700',
    'serial_number' => 'B2WN01000462A3700',
    'name' => 'Ont Iscom Ht-803G-07 ( Pn - )',
    'brand' => 'Raisecom',
    'type' => 'ONT',
  ),
  45 => 
  array (
    'kode_spek' => 'B2WN0150153M',
    'registration_number' => 'B2WN0150153MA1873',
    'serial_number' => 'B2WN0150153MA1873',
    'name' => 'Adaptor 12V 1A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  46 => 
  array (
    'kode_spek' => 'B2WN0286496M',
    'registration_number' => 'B2WN0286496MA0052',
    'serial_number' => 'B2WN0286496MA0052',
    'name' => 'Fortigate 60F',
    'brand' => 'Fortinet',
    'type' => 'Lainnya',
  ),
  47 => 
  array (
    'kode_spek' => 'B2WN0286499M',
    'registration_number' => 'B2WN0286499MA0148',
    'serial_number' => 'B2WN0286499MA0148',
    'name' => 'Ac Power Adaptor For Fg/Fwf 60E/61E, 60F/61F, And 80E/81E',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  48 => 
  array (
    'kode_spek' => 'B2WN0286773M',
    'registration_number' => 'B2WN0286773MA1809',
    'serial_number' => 'B2WN0286773MA1809',
    'name' => 'Optical Network Terminal ( Ont ) Iscom5104G-Gp-Hb-07',
    'brand' => 'Raisecom',
    'type' => 'ONT',
  ),
  49 => 
  array (
    'kode_spek' => 'B2WN0286773M',
    'registration_number' => 'B2WN0286773MA0708',
    'serial_number' => 'B2WN0286773MA0708',
    'name' => 'Optical Network Terminal ( Ont ) Iscom5104G-Gp-Hb-07',
    'brand' => 'Raisecom',
    'type' => 'ONT',
  ),
  50 => 
  array (
    'kode_spek' => 'B2WN0150153M',
    'registration_number' => 'B2WN0150153MA3187',
    'serial_number' => 'B2WN0150153MA3187',
    'name' => 'Adaptor 12V 1A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  51 => 
  array (
    'kode_spek' => 'B2WN0286873M',
    'registration_number' => 'B2WN0286873MA1090B',
    'serial_number' => 'B2WN0286873MA1090B',
    'name' => 'Power Supply o/p 24VDC - 1,2A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  52 => 
  array (
    'kode_spek' => 'B2WN0286873M',
    'registration_number' => 'B2WN0286873MA1089B',
    'serial_number' => 'B2WN0286873MA1089B',
    'name' => 'Power Supply o/p 24VDC - 1,2A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  53 => 
  array (
    'kode_spek' => 'B2WN0150153M',
    'registration_number' => 'B2WN0150153MA5111',
    'serial_number' => 'B2WN0150153MA5111',
    'name' => 'Adaptor 12V 1A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  54 => 
  array (
    'kode_spek' => 'B2WS028N0113',
    'registration_number' => 'B2WS028N0113A0150',
    'serial_number' => 'B2WS028N0113A0150',
    'name' => 'Mikrotik Rb951Ui-2Hnd ( Pn - ) ( Pn - )',
    'brand' => 'MikroTik',
    'type' => 'Lainnya',
  ),
  55 => 
  array (
    'kode_spek' => 'B2WS02VU010G',
    'registration_number' => 'B2WS02VU010GA0938',
    'serial_number' => 'B2WS02VU010GA0938',
    'name' => 'Wibas Connect Poe Injector',
    'brand' => 'Intracom',
    'type' => 'PoE Injector',
  ),
  56 => 
  array (
    'kode_spek' => 'B2WS02VU010G',
    'registration_number' => 'B2WS02VU010GA0133',
    'serial_number' => 'B2WS02VU010GA0133',
    'name' => 'Wibas Connect Poe Injector',
    'brand' => 'Intracom',
    'type' => 'PoE Injector',
  ),
  57 => 
  array (
    'kode_spek' => 'B2WN0286872M',
    'registration_number' => 'B2WN0286872MA1189',
    'serial_number' => 'B2WN0286872MA1189',
    'name' => 'Router Mikrotik Rb760Igs',
    'brand' => 'MikroTik',
    'type' => 'Router',
  ),
  58 => 
  array (
    'kode_spek' => 'B2WN0286872M',
    'registration_number' => 'B2WN0286872MA2060',
    'serial_number' => 'B2WN0286872MA2060',
    'name' => 'Router Mikrotik Rb760Igs',
    'brand' => 'MikroTik',
    'type' => 'Router',
  ),
  59 => 
  array (
    'kode_spek' => 'B2WN0286873M',
    'registration_number' => 'B2WN0286873MA1897B',
    'serial_number' => 'B2WN0286873MA1897B',
    'name' => 'Power Supply o/p 24VDC - 1,2A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  60 => 
  array (
    'kode_spek' => 'B2WS0138115M',
    'registration_number' => 'B2WS0138115MA0161',
    'serial_number' => 'B2WS0138115MA0161',
    'name' => 'Radio Mimosa C5x',
    'brand' => 'Mimosa',
    'type' => 'Radio',
  ),
  61 => 
  array (
    'kode_spek' => 'B2WS0138118M',
    'registration_number' => 'B2WS0138118MA0302',
    'serial_number' => 'B2WS0138118MA0302',
    'name' => 'Mimosa PoE Wall Plug 56V Eu',
    'brand' => 'Mimosa',
    'type' => 'PoE Injector',
  ),
  62 => 
  array (
    'kode_spek' => 'B2WS0391069M',
    'registration_number' => 'B2WS0391069MA0003',
    'serial_number' => 'B2WS0391069MA0003',
    'name' => 'PoE B5c Connectorized PTP',
    'brand' => 'PT DIKTA',
    'type' => 'PoE Injector',
  ),
  63 => 
  array (
    'kode_spek' => 'B2WS0391071M',
    'registration_number' => 'B2WS0391071MA0003',
    'serial_number' => 'B2WS0391071MA0003',
    'name' => 'Mimosa B5c Connectorized PTP',
    'brand' => 'Mimosa',
    'type' => 'Lainnya',
  ),
  64 => 
  array (
    'kode_spek' => 'B2WS020O0110',
    'registration_number' => 'B2WS020O011008620',
    'serial_number' => 'B2WS020O011008620',
    'name' => 'Psu Remote 7000 Series -Idu- C-Band Hughes ( Pn - 1031105-0001 )',
    'brand' => 'Hughes',
    'type' => 'Power Supply',
  ),
  65 => 
  array (
    'kode_spek' => 'USTLWSAJ0102',
    'registration_number' => 'USTLWSAJ010200088',
    'serial_number' => 'USTLWSAJ010200088',
    'name' => 'Uso-Remote Hx50-Idu- C-Band Hughes',
    'brand' => 'Hughes',
    'type' => 'Satellite Remote',
  ),
  66 => 
  array (
    'kode_spek' => 'B2WN0286873M',
    'registration_number' => 'B2WN0286873MA1091B',
    'serial_number' => 'B2WN0286873MA1091B',
    'name' => 'Power Supply o/p 24VDC - 1,2A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  67 => 
  array (
    'kode_spek' => 'B2WN0286872M',
    'registration_number' => 'B2WN0286872MA1191',
    'serial_number' => 'B2WN0286872MA1191',
    'name' => 'Router Mikrotik Rb760Igs',
    'brand' => 'MikroTik',
    'type' => 'Router',
  ),
  68 => 
  array (
    'kode_spek' => 'B2WN0286872M',
    'registration_number' => 'B2WN0286872MA1190',
    'serial_number' => 'B2WN0286872MA1190',
    'name' => 'Router Mikrotik Rb760Igs',
    'brand' => 'MikroTik',
    'type' => 'Router',
  ),
  69 => 
  array (
    'kode_spek' => 'B2WS010Q0102',
    'registration_number' => 'B2WS010Q010208577',
    'serial_number' => 'B2WS010Q010208577',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  70 => 
  array (
    'kode_spek' => 'B2WS010Q0102',
    'registration_number' => 'B2WS010Q010207844',
    'serial_number' => 'B2WS010Q010207844',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  71 => 
  array (
    'kode_spek' => 'B2WS02VU010E',
    'registration_number' => 'B2WS02VU010E00138',
    'serial_number' => 'B2WS02VU010E00138',
    'name' => 'Wibas Connect Fully Outdoor Terminal Station Cpe 10.5 Ghz Hi',
    'brand' => 'Intracom',
    'type' => 'Radio',
  ),
  72 => 
  array (
    'kode_spek' => 'B2WS030O0107',
    'registration_number' => 'B2WS030O010707357',
    'serial_number' => 'B2WS030O010707357',
    'name' => 'Rfu-Excb- Tig-2Wl-F-C-Band',
    'brand' => 'PT DIKTA',
    'type' => 'BUC/RFU',
  ),
  73 => 
  array (
    'kode_spek' => 'B2WS030O0107',
    'registration_number' => 'B2WS030O010708261',
    'serial_number' => 'B2WS030O010708261',
    'name' => 'Rfu-Excb- Tig-2Wl-F-C-Band',
    'brand' => 'PT DIKTA',
    'type' => 'BUC/RFU',
  ),
  74 => 
  array (
    'kode_spek' => 'B2WS030O0107',
    'registration_number' => 'B2WS030O010706109',
    'serial_number' => 'B2WS030O010706109',
    'name' => 'Rfu-Excb- Tig-2Wl-F-C-Band',
    'brand' => 'PT DIKTA',
    'type' => 'BUC/RFU',
  ),
  75 => 
  array (
    'kode_spek' => 'B2WS03VI0103',
    'registration_number' => 'B2WS03VI010306345',
    'serial_number' => 'B2WS03VI010306345',
    'name' => 'Dro Lnb C-Band For Idirect Remote ( Pn - 8220 )',
    'brand' => 'iDirect',
    'type' => 'LNB/LNA',
  ),
  76 => 
  array (
    'kode_spek' => 'B2WS0138158M',
    'registration_number' => 'B2WS0138158MA0532',
    'serial_number' => 'B2WS0138158MA0532',
    'name' => 'Remote Enterprise Flat Hp-Starlink',
    'brand' => 'Starlink',
    'type' => 'Satellite Remote',
  ),
  77 => 
  array (
    'kode_spek' => 'B2WN0286417M',
    'registration_number' => 'B2WN0286417MA0437B',
    'serial_number' => 'B2WN0286417MA0437B',
    'name' => 'Psu 12V, 3A For Fortigate 40 F',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  78 => 
  array (
    'kode_spek' => 'B2WN0286418M',
    'registration_number' => 'B2WN0286418MA1769',
    'serial_number' => 'B2WN0286418MA1769',
    'name' => 'Fortigate 40F',
    'brand' => 'Fortinet',
    'type' => 'Lainnya',
  ),
  79 => 
  array (
    'kode_spek' => 'B1AC29A0029M',
    'registration_number' => 'B1AC29A0029MA0325B',
    'serial_number' => 'B1AC29A0029MA0325B',
    'name' => 'AC Power Adapter 65W FOR ThinkEdge SE30 Edge Client',
    'brand' => 'Lenovo',
    'type' => 'Power Supply',
  ),
  80 => 
  array (
    'kode_spek' => 'B1AC29A0029M',
    'registration_number' => 'B1AC29A0029MA0225B',
    'serial_number' => 'B1AC29A0029MA0225B',
    'name' => 'AC Power Adapter 65W FOR ThinkEdge SE30 Edge Client',
    'brand' => 'Lenovo',
    'type' => 'Power Supply',
  ),
  81 => 
  array (
    'kode_spek' => 'B1AC29A0029M',
    'registration_number' => 'B1AC29A0029MA0314B',
    'serial_number' => 'B1AC29A0029MA0314B',
    'name' => 'AC Power Adapter 65W FOR ThinkEdge SE30 Edge Client',
    'brand' => 'Lenovo',
    'type' => 'Power Supply',
  ),
  82 => 
  array (
    'kode_spek' => 'B1AC29A0029M',
    'registration_number' => 'B1AC29A0029MA0254B',
    'serial_number' => 'B1AC29A0029MA0254B',
    'name' => 'AC Power Adapter 65W FOR ThinkEdge SE30 Edge Client',
    'brand' => 'Lenovo',
    'type' => 'Power Supply',
  ),
  83 => 
  array (
    'kode_spek' => 'B1AC29A0029M',
    'registration_number' => 'B1AC29A0029MA0125B',
    'serial_number' => 'B1AC29A0029MA0125B',
    'name' => 'AC Power Adapter 65W FOR ThinkEdge SE30 Edge Client',
    'brand' => 'Lenovo',
    'type' => 'Power Supply',
  ),
  84 => 
  array (
    'kode_spek' => 'B1AC29A0029M',
    'registration_number' => 'B1AC29A0029MA0137B',
    'serial_number' => 'B1AC29A0029MA0137B',
    'name' => 'AC Power Adapter 65W FOR ThinkEdge SE30 Edge Client',
    'brand' => 'Lenovo',
    'type' => 'Power Supply',
  ),
  85 => 
  array (
    'kode_spek' => 'B1AC29A0029M',
    'registration_number' => 'B1AC29A0029MA0250B',
    'serial_number' => 'B1AC29A0029MA0250B',
    'name' => 'AC Power Adapter 65W FOR ThinkEdge SE30 Edge Client',
    'brand' => 'Lenovo',
    'type' => 'Power Supply',
  ),
  86 => 
  array (
    'kode_spek' => 'B1AC29A0029M',
    'registration_number' => 'B1AC29A0029MA0122B',
    'serial_number' => 'B1AC29A0029MA0122B',
    'name' => 'AC Power Adapter 65W FOR ThinkEdge SE30 Edge Client',
    'brand' => 'Lenovo',
    'type' => 'Power Supply',
  ),
  87 => 
  array (
    'kode_spek' => 'B1AC29A0029M',
    'registration_number' => 'B1AC29A0029MA0312B',
    'serial_number' => 'B1AC29A0029MA0312B',
    'name' => 'AC Power Adapter 65W FOR ThinkEdge SE30 Edge Client',
    'brand' => 'Lenovo',
    'type' => 'Power Supply',
  ),
  88 => 
  array (
    'kode_spek' => 'B1AC29A0029M',
    'registration_number' => 'B1AC29A0029MA031B',
    'serial_number' => 'B1AC29A0029MA031B',
    'name' => 'AC Power Adapter 65W FOR ThinkEdge SE30 Edge Client',
    'brand' => 'Lenovo',
    'type' => 'Power Supply',
  ),
  89 => 
  array (
    'kode_spek' => 'B2WS05A0087M',
    'registration_number' => 'B2WS05A0087MA0140',
    'serial_number' => 'B2WS05A0087MA0140',
    'name' => 'TL-POE160S PoE+ Injector',
    'brand' => 'TP-Link',
    'type' => 'PoE Injector',
  ),
  90 => 
  array (
    'kode_spek' => 'B2WS05A0087M',
    'registration_number' => 'B2WS05A0087MA0110',
    'serial_number' => 'B2WS05A0087MA0110',
    'name' => 'TL-POE160S PoE+ Injector',
    'brand' => 'TP-Link',
    'type' => 'PoE Injector',
  ),
  91 => 
  array (
    'kode_spek' => 'B2WS05A0087M',
    'registration_number' => 'B2WS05A0087MA0107',
    'serial_number' => 'B2WS05A0087MA0107',
    'name' => 'TL-POE160S PoE+ Injector',
    'brand' => 'TP-Link',
    'type' => 'PoE Injector',
  ),
  92 => 
  array (
    'kode_spek' => 'B2WS05A0087M',
    'registration_number' => 'B2WS05A0087MA0106',
    'serial_number' => 'B2WS05A0087MA0106',
    'name' => 'TL-POE160S PoE+ Injector',
    'brand' => 'TP-Link',
    'type' => 'PoE Injector',
  ),
  93 => 
  array (
    'kode_spek' => 'B2WN13A0059M',
    'registration_number' => 'B2WN13A0059MA0010',
    'serial_number' => 'B2WN13A0059MA0010',
    'name' => 'Meraki Mr44 Wifi 6 Indoor Ap',
    'brand' => 'Cisco',
    'type' => 'Lainnya',
  ),
  94 => 
  array (
    'kode_spek' => 'B2WN0286418M',
    'registration_number' => 'B2WN0286418MA1906',
    'serial_number' => 'B2WN0286418MA1906',
    'name' => 'Fortigate 40F',
    'brand' => 'Fortinet',
    'type' => 'Lainnya',
  ),
  95 => 
  array (
    'kode_spek' => 'B2WN0286417M',
    'registration_number' => 'B2WN0286417MA0507B',
    'serial_number' => 'B2WN0286417MA0507B',
    'name' => 'Psu 12V, 3A For Fortigate 40 F',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  96 => 
  array (
    'kode_spek' => 'B2WN0150266M',
    'registration_number' => 'B2WN0150266MA0012',
    'serial_number' => 'B2WN0150266MA0012',
    'name' => 'L2+ managed POE switch with 24GE + 4SFP+, 24port POE with max 370W limit and smart',
    'brand' => 'PT DIKTA',
    'type' => 'PoE Injector',
  ),
  97 => 
  array (
    'kode_spek' => 'B2WN0286894M',
    'registration_number' => 'B2WN0286894MA0073',
    'serial_number' => 'B2WN0286894MA0073',
    'name' => 'Versa Certified Hardware V110, Versa 2C(V100) Rcc-Ve2, C2558 Cpu, 4Gb Ddr, Ecc, 32Gb Flash',
    'brand' => 'Versa',
    'type' => 'Lainnya',
  ),
  98 => 
  array (
    'kode_spek' => 'B2WN0286418M',
    'registration_number' => 'B2WN0286418MA1071',
    'serial_number' => 'B2WN0286418MA1071',
    'name' => 'Fortigate 40F',
    'brand' => 'Fortinet',
    'type' => 'Lainnya',
  ),
  99 => 
  array (
    'kode_spek' => 'B2WN0286417M',
    'registration_number' => 'B2WN0286417MA1071',
    'serial_number' => 'B2WN0286417MA1071',
    'name' => 'Psu 12V, 3A For Fortigate 40 F',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  100 => 
  array (
    'kode_spek' => 'B2WN0286290M',
    'registration_number' => 'B2WN0286290MA0075',
    'serial_number' => 'B2WN0286290MA0075',
    'name' => 'Mikrotik Ltap Mini Lte Kit - Rb912R-2Nd-Ltm&R11E-Lte',
    'brand' => 'MikroTik',
    'type' => 'Lainnya',
  ),
  101 => 
  array (
    'kode_spek' => 'B2WS02VU010F',
    'registration_number' => 'B2WS02VU010F01142',
    'serial_number' => 'B2WS02VU010F01142',
    'name' => 'Wibas Connect Panel Antenna 10.5 Ghz',
    'brand' => 'Intracom',
    'type' => 'Antenna',
  ),
  102 => 
  array (
    'kode_spek' => 'B2WN0100041X',
    'registration_number' => 'B2WN0100041XA2045',
    'serial_number' => 'B2WN0100041XA2045',
    'name' => '3Rd Party Gpon Ont With 4Ge + 2 Pots ( Pn - 178-80003 )',
    'brand' => 'PT DIKTA',
    'type' => 'ONT',
  ),
  103 => 
  array (
    'kode_spek' => 'B2WN01000440',
    'registration_number' => 'B2WN0100044000004',
    'serial_number' => 'B2WN0100044000004',
    'name' => 'Ont 4Ge + 2 Post + Wifi (3Rd Prty Cig) ( Pn - )',
    'brand' => 'PT DIKTA',
    'type' => 'ONT',
  ),
  104 => 
  array (
    'kode_spek' => 'B2WN0286773M',
    'registration_number' => 'B2WN0286773MA6376',
    'serial_number' => 'B2WN0286773MA6376',
    'name' => 'Optical Network Terminal ( Ont ) Iscom5104G-Gp-Hb-07',
    'brand' => 'Raisecom',
    'type' => 'ONT',
  ),
  105 => 
  array (
    'kode_spek' => 'B2WN0150153M',
    'registration_number' => 'B2WN0150153MA3585B',
    'serial_number' => 'B2WN0150153MA3585B',
    'name' => 'Adaptor 12V 1A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  106 => 
  array (
    'kode_spek' => 'B2WN0286290M',
    'registration_number' => 'B2WN0286290MA0321',
    'serial_number' => 'B2WN0286290MA0321',
    'name' => 'Mikrotik Ltap Mini Lte Kit - Rb912R-2Nd-Ltm&R11E-Lte',
    'brand' => 'MikroTik',
    'type' => 'Lainnya',
  ),
  107 => 
  array (
    'kode_spek' => 'B2WS03440102',
    'registration_number' => 'B2WS0344010200034',
    'serial_number' => 'B2WS0344010200034',
    'name' => 'PROXIM POE GIGA BIT',
    'brand' => 'Proxim',
    'type' => 'PoE Injector',
  ),
  108 => 
  array (
    'kode_spek' => 'B2WN0286417M',
    'registration_number' => 'B2WN0286417MA0076',
    'serial_number' => 'B2WN0286417MA0076',
    'name' => 'Psu 12V, 3A For Fortigate 40 F',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  109 => 
  array (
    'kode_spek' => 'B2WN0262020G',
    'registration_number' => 'B2WN0262020G00887',
    'serial_number' => 'B2WN0262020G00887',
    'name' => 'Fortigate-30E Hw And Warranty',
    'brand' => 'Fortinet',
    'type' => 'Lainnya',
  ),
  110 => 
  array (
    'kode_spek' => 'B2WN0262020H',
    'registration_number' => 'B2WN0262020HA1414',
    'serial_number' => 'B2WN0262020HA1414',
    'name' => 'Fortigate-50E Hw And Warranty',
    'brand' => 'Fortinet',
    'type' => 'Lainnya',
  ),
  111 => 
  array (
    'kode_spek' => 'B2WN00A0030M',
    'registration_number' => 'B2WN00A0030MA0004B',
    'serial_number' => 'B2WN00A0030MA0004B',
    'name' => 'AC Power Adaptor for FG/FWF-40F Project Lambda',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  112 => 
  array (
    'kode_spek' => 'B2WN0286208M',
    'registration_number' => 'B2WN0286208MA0024I',
    'serial_number' => 'B2WN0286208MA0024I',
    'name' => 'Psu Forti Fg-50E/Fg-30E',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  113 => 
  array (
    'kode_spek' => 'B2WN0202011N',
    'registration_number' => 'B2WN0202011N01137',
    'serial_number' => 'B2WN0202011N01137',
    'name' => 'Next Generation Carrier Ethernet Demarcation Device',
    'brand' => 'PT DIKTA',
    'type' => 'Interface Module',
  ),
  114 => 
  array (
    'kode_spek' => 'B2WS02VU010E',
    'registration_number' => 'B2WS02VU010E00154',
    'serial_number' => 'B2WS02VU010E00154',
    'name' => 'Wibas Connect Fully Outdoor Terminal Station Cpe 10.5 Ghz Hi',
    'brand' => 'Intracom',
    'type' => 'Radio',
  ),
  115 => 
  array (
    'kode_spek' => 'B2WS020O0110',
    'registration_number' => 'B2WS020O011010693',
    'serial_number' => 'B2WS020O011010693',
    'name' => 'Psu Remote 7000 Series -Idu- C-Band Hughes ( Pn - 1031105-0001 )',
    'brand' => 'Hughes',
    'type' => 'Power Supply',
  ),
  116 => 
  array (
    'kode_spek' => 'B2WS0391020H',
    'registration_number' => 'B2WS0391020H00625',
    'serial_number' => 'B2WS0391020H00625',
    'name' => 'Ac Poe Device Power Radwin ( Pn - 9921W1011 )',
    'brand' => 'Radwin',
    'type' => 'PoE Injector',
  ),
  117 => 
  array (
    'kode_spek' => 'B4CP0113433M',
    'registration_number' => 'B4CP0113433MA0023',
    'serial_number' => 'B4CP0113433MA0023',
    'name' => 'Poe Injector Ap 720 Rujije Tambahan 2023 (Rg-Poe-Af15)',
    'brand' => 'PT DIKTA',
    'type' => 'PoE Injector',
  ),
  118 => 
  array (
    'kode_spek' => 'B2WS020O0110',
    'registration_number' => 'B2WS020O011002704',
    'serial_number' => 'B2WS020O011002704',
    'name' => 'Psu Remote 7000 Series -Idu- C-Band Hughes ( Pn - 1031105-0001 )',
    'brand' => 'Hughes',
    'type' => 'Power Supply',
  ),
  119 => 
  array (
    'kode_spek' => 'B2WN0202011N',
    'registration_number' => 'B2WN0202011N01328',
    'serial_number' => 'B2WN0202011N01328',
    'name' => 'Next Generation Carrier Ethernet Demarcation Device',
    'brand' => 'PT DIKTA',
    'type' => 'Interface Module',
  ),
  120 => 
  array (
    'kode_spek' => 'B2WN00A0363M',
    'registration_number' => 'B2WN00A0363MA0267',
    'serial_number' => 'B2WN00A0363MA0267',
    'name' => 'Transceiver BIDI SFP 1.25G Tx1310nm 20km DDM',
    'brand' => 'PT DIKTA',
    'type' => 'SFP Transceiver',
  ),
  121 => 
  array (
    'kode_spek' => 'B2WN00A0367M',
    'registration_number' => 'B2WN00A0367MA0353',
    'serial_number' => 'B2WN00A0367MA0353',
    'name' => 'Transceiver BIDI SFP 1.25G Tx1550nm 20km DDM',
    'brand' => 'PT DIKTA',
    'type' => 'SFP Transceiver',
  ),
  122 => 
  array (
    'kode_spek' => 'B2WS03VI0102',
    'registration_number' => 'B2WS03VI010201544',
    'serial_number' => 'B2WS03VI010201544',
    'name' => '2W C-Band Buc For Idirect Remote ( Pn - Njt5667F )',
    'brand' => 'iDirect',
    'type' => 'BUC/RFU',
  ),
  123 => 
  array (
    'kode_spek' => 'B2WS03VI0102',
    'registration_number' => 'B2WS03VI010201285',
    'serial_number' => 'B2WS03VI010201285',
    'name' => '2W C-Band Buc For Idirect Remote ( Pn - Njt5667F )',
    'brand' => 'iDirect',
    'type' => 'BUC/RFU',
  ),
  124 => 
  array (
    'kode_spek' => 'B2WS02A70202',
    'registration_number' => 'B2WS02A7020201916',
    'serial_number' => 'B2WS02A7020201916',
    'name' => 'Psu For Evolution X3 Satelite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Power Supply',
  ),
  125 => 
  array (
    'kode_spek' => 'B2WS02A70203',
    'registration_number' => 'B2WS02A7020301211',
    'serial_number' => 'B2WS02A7020301211',
    'name' => 'Evolution X1 Satellite Router',
    'brand' => 'iDirect',
    'type' => 'Router',
  ),
  126 => 
  array (
    'kode_spek' => 'B2WN0286418M',
    'registration_number' => 'B2WN0286418MA0042',
    'serial_number' => 'B2WN0286418MA0042',
    'name' => 'Fortigate 40F',
    'brand' => 'Fortinet',
    'type' => 'Lainnya',
  ),
  127 => 
  array (
    'kode_spek' => 'B2WN0231016U',
    'registration_number' => 'B2WN0231016U00210',
    'serial_number' => 'B2WN0231016U00210',
    'name' => '1-PORT SERIAL WAN INTERFACE CARD',
    'brand' => 'PT DIKTA',
    'type' => 'Interface Module',
  ),
  128 => 
  array (
    'kode_spek' => 'B2WN0231010C',
    'registration_number' => 'B2WN0231010C00149',
    'serial_number' => 'B2WN0231010C00149',
    'name' => '2 PORT SERIAL WAN INTERFACE CARD FOR CISCO 2600',
    'brand' => 'Cisco',
    'type' => 'Interface Module',
  ),
  129 => 
  array (
    'kode_spek' => 'B2WN0286773M',
    'registration_number' => 'B2WN0286773MA6185',
    'serial_number' => 'B2WN0286773MA6185',
    'name' => 'Optical Network Terminal ( Ont ) Iscom5104G-Gp-Hb-07',
    'brand' => 'Raisecom',
    'type' => 'ONT',
  ),
  130 => 
  array (
    'kode_spek' => 'B2WN0150153M',
    'registration_number' => 'B2WN0150153MA3777B',
    'serial_number' => 'B2WN0150153MA3777B',
    'name' => 'Adaptor 12V 1A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  131 => 
  array (
    'kode_spek' => 'B2WS02A70201',
    'registration_number' => 'B2WS02A7020100839',
    'serial_number' => 'B2WS02A7020100839',
    'name' => 'Evolution X3 Satellite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Router',
  ),
  132 => 
  array (
    'kode_spek' => 'B2WS02A70202',
    'registration_number' => 'B2WS02A7020201989',
    'serial_number' => 'B2WS02A7020201989',
    'name' => 'Psu For Evolution X3 Satelite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Power Supply',
  ),
  133 => 
  array (
    'kode_spek' => 'B2WS0391052M',
    'registration_number' => 'B2WS0391052MA1332',
    'serial_number' => 'B2WS0391052MA1332',
    'name' => 'Cambium Force-200, 5 Ghz, High Gain 25 Dbi Antenna, Up To 200+Mbps Throughput',
    'brand' => 'Cambium Networks',
    'type' => 'Antenna',
  ),
  134 => 
  array (
    'kode_spek' => 'B2WS0391048M',
    'registration_number' => 'B2WS0391048MA1332',
    'serial_number' => 'B2WS0391048MA1332',
    'name' => 'Poe Cambium Force 200',
    'brand' => 'Cambium Networks',
    'type' => 'PoE Injector',
  ),
  135 => 
  array (
    'kode_spek' => 'B2WS02A70202',
    'registration_number' => 'B2WS02A7020200186',
    'serial_number' => 'B2WS02A7020200186',
    'name' => 'Psu For Evolution X3 Satelite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Power Supply',
  ),
  136 => 
  array (
    'kode_spek' => 'B2WS02A70202',
    'registration_number' => 'B2WS02A70202A0032',
    'serial_number' => 'B2WS02A70202A0032',
    'name' => 'Psu For Evolution X3 Satelite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Power Supply',
  ),
  137 => 
  array (
    'kode_spek' => 'B2WS02A70202',
    'registration_number' => 'B2WS02A7020201556',
    'serial_number' => 'B2WS02A7020201556',
    'name' => 'Psu For Evolution X3 Satelite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Power Supply',
  ),
  138 => 
  array (
    'kode_spek' => 'B2WS02A70201',
    'registration_number' => 'B2WS02A7020102049',
    'serial_number' => 'B2WS02A7020102049',
    'name' => 'Evolution X3 Satellite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Router',
  ),
  139 => 
  array (
    'kode_spek' => 'B2WS02A70201',
    'registration_number' => 'B2WS02A7020101692',
    'serial_number' => 'B2WS02A7020101692',
    'name' => 'Evolution X3 Satellite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Router',
  ),
  140 => 
  array (
    'kode_spek' => 'B2WS02A70201',
    'registration_number' => 'B2WS02A7020100322',
    'serial_number' => 'B2WS02A7020100322',
    'name' => 'Evolution X3 Satellite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Router',
  ),
  141 => 
  array (
    'kode_spek' => 'B2WN0177010F',
    'registration_number' => 'B2WN0177010F00117',
    'serial_number' => 'B2WN0177010F00117',
    'name' => 'Fiber Driver Eth Raisecom Rc552-Fe-Ss13 ( Pn - )',
    'brand' => 'Raisecom',
    'type' => 'Lainnya',
  ),
  142 => 
  array (
    'kode_spek' => 'B2WN01AH010H',
    'registration_number' => 'B2WN01AH010H00028',
    'serial_number' => 'B2WN01AH010H00028',
    'name' => 'Raisecom Module Ge With 1:1 Uplink Protection 2 Port Sfp',
    'brand' => 'Raisecom',
    'type' => 'SFP Transceiver',
  ),
  143 => 
  array (
    'kode_spek' => 'B2WN0287084M',
    'registration_number' => 'B2WN0287084MA0097',
    'serial_number' => 'B2WN0287084MA0097',
    'name' => 'Intelligent Ethernet Demarcation Device/Router consisting Compact 10G NID, half-19\'\' wide,',
    'brand' => 'PT DIKTA',
    'type' => 'Router',
  ),
  144 => 
  array (
    'kode_spek' => 'B2WN0286405M',
    'registration_number' => 'B2WN0286405MA0303B',
    'serial_number' => 'B2WN0286405MA0303B',
    'name' => 'Ac Power Supply For Rax711-C-R Series',
    'brand' => 'Raisecom',
    'type' => 'Power Supply',
  ),
  145 => 
  array (
    'kode_spek' => 'B2WS02VU010G',
    'registration_number' => 'B2WS02VU010G00319',
    'serial_number' => 'B2WS02VU010G00319',
    'name' => 'Wibas Connect Poe Injector',
    'brand' => 'Intracom',
    'type' => 'PoE Injector',
  ),
  146 => 
  array (
    'kode_spek' => 'B2WS02VU010G',
    'registration_number' => 'B2WS02VU010G00616',
    'serial_number' => 'B2WS02VU010G00616',
    'name' => 'Wibas Connect Poe Injector',
    'brand' => 'Intracom',
    'type' => 'PoE Injector',
  ),
  147 => 
  array (
    'kode_spek' => 'B2WN0150153M',
    'registration_number' => 'B2WN0150153MB1502',
    'serial_number' => 'B2WN0150153MB1502',
    'name' => 'Adaptor 12V 1A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  148 => 
  array (
    'kode_spek' => 'B2WN0150153M',
    'registration_number' => 'B2WN0150153MA0451',
    'serial_number' => 'B2WN0150153MA0451',
    'name' => 'Adaptor 12V 1A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  149 => 
  array (
    'kode_spek' => 'B2WS0391128M',
    'registration_number' => 'B2WS0391128MA0015',
    'serial_number' => 'B2WS0391128MA0015',
    'name' => 'NJRC LNA C-BAND SCPC',
    'brand' => 'NJRC',
    'type' => 'LNB/LNA',
  ),
  150 => 
  array (
    'kode_spek' => 'B2WS0200122M',
    'registration_number' => 'B2WS0200122MA0010',
    'serial_number' => 'B2WS0200122MA0010',
    'name' => 'Remote Intracom 10,5 ( Wibas Connect ) Wibas Terminal Station, 10,5Ghz , 1Xgbf',
    'brand' => 'Intracom',
    'type' => 'Radio',
  ),
  151 => 
  array (
    'kode_spek' => 'B2WS0100010H',
    'registration_number' => 'B2WS0100010H00628',
    'serial_number' => 'B2WS0100010H00628',
    'name' => 'Lna Vsat Link',
    'brand' => 'PT DIKTA',
    'type' => 'LNB/LNA',
  ),
  152 => 
  array (
    'kode_spek' => 'B2WS033A0103',
    'registration_number' => 'B2WS033A010300253',
    'serial_number' => 'B2WS033A010300253',
    'name' => 'Odu-Sp 13 Ghz Low Nera ( Pn - )',
    'brand' => 'PT DIKTA',
    'type' => 'Lainnya',
  ),
  153 => 
  array (
    'kode_spek' => 'B2WN01310468',
    'registration_number' => 'B2WN0131046800014',
    'serial_number' => 'B2WN0131046800014',
    'name' => 'TWO-PORT VOICE INTERFACE CARD- FXS AND DID',
    'brand' => 'PT DIKTA',
    'type' => 'Lainnya',
  ),
  154 => 
  array (
    'kode_spek' => 'B2WN0231017C',
    'registration_number' => 'B2WN0231017C00063',
    'serial_number' => 'B2WN0231017C00063',
    'name' => '4 PORT VIC3-4FXS DAN DID',
    'brand' => 'PT DIKTA',
    'type' => 'Interface Module',
  ),
  155 => 
  array (
    'kode_spek' => 'B2WS0391204M',
    'registration_number' => 'B2WS0391204MA0214',
    'serial_number' => 'B2WS0391204MA0214',
    'name' => 'Cambium Force-300L, 5 Ghz, High Gain 25 Dbi Antenna, Up To 400+Mbps Throughput',
    'brand' => 'Cambium Networks',
    'type' => 'Antenna',
  ),
  156 => 
  array (
    'kode_spek' => 'B2WS0391052M',
    'registration_number' => 'B2WS0391052MA1368',
    'serial_number' => 'B2WS0391052MA1368',
    'name' => 'Cambium Force-200, 5 Ghz, High Gain 25 Dbi Antenna, Up To 200+Mbps Throughput',
    'brand' => 'Cambium Networks',
    'type' => 'Antenna',
  ),
  157 => 
  array (
    'kode_spek' => 'B2WS0391204M',
    'registration_number' => 'B2WS0391204MA0006',
    'serial_number' => 'B2WS0391204MA0006',
    'name' => 'Cambium Force-300L, 5 Ghz, High Gain 25 Dbi Antenna, Up To 400+Mbps Throughput',
    'brand' => 'Cambium Networks',
    'type' => 'Antenna',
  ),
  158 => 
  array (
    'kode_spek' => 'B2WS0391052M',
    'registration_number' => 'B2WS0391052MA1272',
    'serial_number' => 'B2WS0391052MA1272',
    'name' => 'Cambium Force-200, 5 Ghz, High Gain 25 Dbi Antenna, Up To 200+Mbps Throughput',
    'brand' => 'Cambium Networks',
    'type' => 'Antenna',
  ),
  159 => 
  array (
    'kode_spek' => 'B2WN0100041X',
    'registration_number' => 'B2WN0100041X03872',
    'serial_number' => 'B2WN0100041X03872',
    'name' => '3Rd Party Gpon Ont With 4Ge + 2 Pots ( Pn - 178-80003 )',
    'brand' => 'PT DIKTA',
    'type' => 'ONT',
  ),
  160 => 
  array (
    'kode_spek' => 'B2WN01310478',
    'registration_number' => 'B2WN0131047800057',
    'serial_number' => 'B2WN0131047800057',
    'name' => 'Cisco 4321+Waas',
    'brand' => 'Cisco',
    'type' => 'Lainnya',
  ),
  161 => 
  array (
    'kode_spek' => 'B2WS020O0116',
    'registration_number' => 'B2WS020O011608146',
    'serial_number' => 'B2WS020O011608146',
    'name' => 'Remote Hx50-Idu- C-Band Hughes',
    'brand' => 'Hughes',
    'type' => 'Satellite Remote',
  ),
  162 => 
  array (
    'kode_spek' => 'B2WS020O0110',
    'registration_number' => 'B2WS020O011011571',
    'serial_number' => 'B2WS020O011011571',
    'name' => 'Psu Remote 7000 Series -Idu- C-Band Hughes ( Pn - 1031105-0001 )',
    'brand' => 'Hughes',
    'type' => 'Power Supply',
  ),
  163 => 
  array (
    'kode_spek' => 'B2WN0286830M',
    'registration_number' => 'B2WN0286830MA0447',
    'serial_number' => 'B2WN0286830MA0447',
    'name' => 'Tabletop, With Fan Nca-1515B-4C-8D-64S',
    'brand' => 'PT DIKTA',
    'type' => 'Lainnya',
  ),
  164 => 
  array (
    'kode_spek' => 'B2WN0286829M',
    'registration_number' => 'B2WN0286829MA0340B',
    'serial_number' => 'B2WN0286829MA0340B',
    'name' => 'Power Adapter Nca-1515B 12V=5A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  165 => 
  array (
    'kode_spek' => 'B2WS02A70201',
    'registration_number' => 'B2WS02A7020101575',
    'serial_number' => 'B2WS02A7020101575',
    'name' => 'Evolution X3 Satellite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Router',
  ),
  166 => 
  array (
    'kode_spek' => 'B2WS020O0110',
    'registration_number' => 'B2WS020O0110A0386',
    'serial_number' => 'B2WS020O0110A0386',
    'name' => 'Psu Remote 7000 Series -Idu- C-Band Hughes ( Pn - 1031105-0001 )',
    'brand' => 'Hughes',
    'type' => 'Power Supply',
  ),
  167 => 
  array (
    'kode_spek' => 'B2WS02A70202',
    'registration_number' => 'B2WS02A7020201606',
    'serial_number' => 'B2WS02A7020201606',
    'name' => 'Psu For Evolution X3 Satelite Router ( Pn - )',
    'brand' => 'iDirect',
    'type' => 'Power Supply',
  ),
  168 => 
  array (
    'kode_spek' => 'B2WN0150107M',
    'registration_number' => 'B2WN0150107MA0091',
    'serial_number' => 'B2WN0150107MA0091',
    'name' => 'Ac Power Supply For Cisco Isr 4320',
    'brand' => 'Cisco',
    'type' => 'Power Supply',
  ),
  169 => 
  array (
    'kode_spek' => 'B2WS010Q010T',
    'registration_number' => 'B2WS010Q010T02889',
    'serial_number' => 'B2WS010Q010T02889',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  170 => 
  array (
    'kode_spek' => 'B2WN0131043M',
    'registration_number' => 'B2WN0131043M00061',
    'serial_number' => 'B2WN0131043M00061',
    'name' => 'Cisco 2921,Sre 700,Waas Eterprise Medium And Sec',
    'brand' => 'Cisco',
    'type' => 'Lainnya',
  ),
  171 => 
  array (
    'kode_spek' => 'B2WN0262020H',
    'registration_number' => 'B2WN0262020HA0831',
    'serial_number' => 'B2WN0262020HA0831',
    'name' => 'Fortigate-50E Hw And Warranty',
    'brand' => 'Fortinet',
    'type' => 'Lainnya',
  ),
  172 => 
  array (
    'kode_spek' => 'B2WN0286208M',
    'registration_number' => 'B2WN0286208MA1029',
    'serial_number' => 'B2WN0286208MA1029',
    'name' => 'Psu Forti Fg-50E/Fg-30E',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  173 => 
  array (
    'kode_spek' => 'B2WN0286773M',
    'registration_number' => 'B2WN0286773MA4129',
    'serial_number' => 'B2WN0286773MA4129',
    'name' => 'Optical Network Terminal ( Ont ) Iscom5104G-Gp-Hb-07',
    'brand' => 'Raisecom',
    'type' => 'ONT',
  ),
  174 => 
  array (
    'kode_spek' => 'B2WN0150153M',
    'registration_number' => 'B2WN0150153MB1996',
    'serial_number' => 'B2WN0150153MB1996',
    'name' => 'Adaptor 12V 1A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  175 => 
  array (
    'kode_spek' => 'B2WN01AH0204',
    'registration_number' => 'B2WN01AH020401891',
    'serial_number' => 'B2WN01AH020401891',
    'name' => 'Raisecom 1U, 1-Slot Chassis, With One Ac Power Supply Connector ( Pn - Rc001-1Ac )',
    'brand' => 'Raisecom',
    'type' => 'Power Supply',
  ),
  176 => 
  array (
    'kode_spek' => 'B2WN01AH010H',
    'registration_number' => 'B2WN01AH010H01013',
    'serial_number' => 'B2WN01AH010H01013',
    'name' => 'Raisecom Module Ge With 1:1 Uplink Protection 2 Port Sfp',
    'brand' => 'Raisecom',
    'type' => 'SFP Transceiver',
  ),
  177 => 
  array (
    'kode_spek' => 'B2WN0287010M',
    'registration_number' => 'B2WN0287010MA0228',
    'serial_number' => 'B2WN0287010MA0228',
    'name' => 'Versa Ready 4 Core 8Gb Ram 16G Ammc',
    'brand' => 'Versa',
    'type' => 'Lainnya',
  ),
  178 => 
  array (
    'kode_spek' => 'B2WN0202011N',
    'registration_number' => 'B2WN0202011N00081',
    'serial_number' => 'B2WN0202011N00081',
    'name' => 'Next Generation Carrier Ethernet Demarcation Device',
    'brand' => 'PT DIKTA',
    'type' => 'Interface Module',
  ),
  179 => 
  array (
    'kode_spek' => 'B2WS020O0116',
    'registration_number' => 'B2WS020O011605737',
    'serial_number' => 'B2WS020O011605737',
    'name' => 'Remote Hx50-Idu- C-Band Hughes',
    'brand' => 'Hughes',
    'type' => 'Satellite Remote',
  ),
  180 => 
  array (
    'kode_spek' => 'B2WS020O0110',
    'registration_number' => 'B2WS020O011009966',
    'serial_number' => 'B2WS020O011009966',
    'name' => 'Psu Remote 7000 Series -Idu- C-Band Hughes ( Pn - 1031105-0001 )',
    'brand' => 'Hughes',
    'type' => 'Power Supply',
  ),
  181 => 
  array (
    'kode_spek' => 'B2WS020O0110',
    'registration_number' => 'B2WS020O011005384',
    'serial_number' => 'B2WS020O011005384',
    'name' => 'Psu Remote 7000 Series -Idu- C-Band Hughes ( Pn - 1031105-0001 )',
    'brand' => 'Hughes',
    'type' => 'Power Supply',
  ),
  182 => 
  array (
    'kode_spek' => 'B3CD115R0105',
    'registration_number' => 'B3CD115R010500409',
    'serial_number' => 'B3CD115R010500409',
    'name' => 'Isolation Transformer (It) 600Va ( Pn - )',
    'brand' => 'PT DIKTA',
    'type' => 'Lainnya',
  ),
  183 => 
  array (
    'kode_spek' => 'B2WN0262020H',
    'registration_number' => 'B2WN0262020HA0795',
    'serial_number' => 'B2WN0262020HA0795',
    'name' => 'Fortigate-50E Hw And Warranty',
    'brand' => 'Fortinet',
    'type' => 'Lainnya',
  ),
  184 => 
  array (
    'kode_spek' => 'B2WN0286208M',
    'registration_number' => 'B2WN0286208MA0827',
    'serial_number' => 'B2WN0286208MA0827',
    'name' => 'Psu Forti Fg-50E/Fg-30E',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  185 => 
  array (
    'kode_spek' => 'B2WN0286873M',
    'registration_number' => 'B2WN0286873MA2399B',
    'serial_number' => 'B2WN0286873MA2399B',
    'name' => 'Power Supply o/p 24VDC - 1,2A',
    'brand' => 'PT DIKTA',
    'type' => 'Power Supply',
  ),
  186 => 
  array (
    'kode_spek' => 'B2WN0286417M',
    'registration_number' => 'B2WN0286417MA1179',
    'serial_number' => 'B2WN0286417MA1179',
    'name' => 'Psu 12V, 3A For Fortigate 40 F',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  187 => 
  array (
    'kode_spek' => 'B2WN00A0030M',
    'registration_number' => 'B2WN00A0030MA0445B',
    'serial_number' => 'B2WN00A0030MA0445B',
    'name' => 'AC Power Adaptor for FG/FWF-40F Project Lambda',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  188 => 
  array (
    'kode_spek' => 'B2WN00A0392M',
    'registration_number' => 'B2WN00A0392MA0394',
    'serial_number' => 'B2WN00A0392MA0394',
    'name' => '4 x GE RJ45 ports (including 3 x Internal Ports, 1 x WAN Ports)',
    'brand' => 'PT DIKTA',
    'type' => 'Lainnya',
  ),
  189 => 
  array (
    'kode_spek' => 'B2WN00A0391M',
    'registration_number' => 'B2WN00A0391MA0331B',
    'serial_number' => 'B2WN00A0391MA0331B',
    'name' => 'AC power adaptors for FG/FWF-30G, come with interchangeable power plugs',
    'brand' => 'Fortinet',
    'type' => 'Power Supply',
  ),
  190 => 
  array (
    'kode_spek' => 'B2WS0138161M',
    'registration_number' => 'B2WS0138161MA0763',
    'serial_number' => 'B2WS0138161MA0763',
    'name' => 'Standard V4',
    'brand' => 'PT DIKTA',
    'type' => 'Lainnya',
  ),
  191 => 
  array (
    'kode_spek' => 'B2WS0138162M',
    'registration_number' => 'B2WS0138162MA0862B',
    'serial_number' => 'B2WS0138162MA0862B',
    'name' => 'Router Starlink V4',
    'brand' => 'Starlink',
    'type' => 'Router',
  ),
  192 => 
  array (
    'kode_spek' => 'B2WS00A0013M',
    'registration_number' => 'B2WS00A0013MA0956B',
    'serial_number' => 'B2WS00A0013MA0956B',
    'name' => 'Power Supply Starlink V4',
    'brand' => 'Starlink',
    'type' => 'Power Supply',
  ),
  193 => 
  array (
    'kode_spek' => 'B2WN0286872M',
    'registration_number' => 'B2WN0286872MA2188',
    'serial_number' => 'B2WN0286872MA2188',
    'name' => 'Router Mikrotik Rb760Igs',
    'brand' => 'MikroTik',
    'type' => 'Router',
  ),
  194 => 
  array (
    'kode_spek' => 'B2WN0287000M',
    'registration_number' => 'B2WN0287000MA0326',
    'serial_number' => 'B2WN0287000MA0326',
    'name' => 'Ethernet Switch Demarcation Device, Consisting 2Xge Optical (Sfp) Nni, 4Xge Electrical (Rj45)',
    'brand' => 'PT DIKTA',
    'type' => 'Switch',
  ),
  195 => 
  array (
    'kode_spek' => 'B2WN0150049M',
    'registration_number' => 'B2WN0150049MA0392B',
    'serial_number' => 'B2WN0150049MA0392B',
    'name' => 'Dc Power Supply For Rax 711 Series',
    'brand' => 'Raisecom',
    'type' => 'Power Supply',
  ),
  196 => 
  array (
    'kode_spek' => 'B2WN0150048M',
    'registration_number' => 'B2WN0150048MA0444B',
    'serial_number' => 'B2WN0150048MA0444B',
    'name' => 'Ac Power Supply For Rax 711 Series',
    'brand' => 'Raisecom',
    'type' => 'Power Supply',
  ),
  197 => 
  array (
    'kode_spek' => 'B2WN0287000M',
    'registration_number' => 'B2WN0287000MA0325',
    'serial_number' => 'B2WN0287000MA0325',
    'name' => 'Ethernet Switch Demarcation Device, Consisting 2Xge Optical (Sfp) Nni, 4Xge Electrical (Rj45)',
    'brand' => 'PT DIKTA',
    'type' => 'Switch',
  ),
  198 => 
  array (
    'kode_spek' => 'B2WN0150049M',
    'registration_number' => 'B2WN0150049MA0430B',
    'serial_number' => 'B2WN0150049MA0430B',
    'name' => 'Dc Power Supply For Rax 711 Series',
    'brand' => 'Raisecom',
    'type' => 'Power Supply',
  ),
  199 => 
  array (
    'kode_spek' => 'B2WN0150048M',
    'registration_number' => 'B2WN0150048MA0423B',
    'serial_number' => 'B2WN0150048MA0423B',
    'name' => 'Ac Power Supply For Rax 711 Series',
    'brand' => 'Raisecom',
    'type' => 'Power Supply',
  ),
  200 => 
  array (
    'kode_spek' => 'B2WN0287082M',
    'registration_number' => 'B2WN0287082MA0052B',
    'serial_number' => 'B2WN0287082MA0052B',
    'name' => 'AC Power Supply for Cisco ISR 4320 series Project Lambda',
    'brand' => 'Cisco',
    'type' => 'Power Supply',
  ),
  201 => 
  array (
    'kode_spek' => 'B2WN0287081M',
    'registration_number' => 'B2WN0287081MA0040',
    'serial_number' => 'B2WN0287081MA0040',
    'name' => 'Cisco ISR 4321 (2GE,2NIM,4G FLASH,4G DRAM,IPB)',
    'brand' => 'Cisco',
    'type' => 'Interface Module',
  ),
  202 => 
  array (
    'kode_spek' => 'B2WN00A0092M',
    'registration_number' => 'B2WN00A0092MA0040',
    'serial_number' => 'B2WN00A0092MA0040',
    'name' => '2-port GE WAN NIM, dual-mode RJ45 & SFP',
    'brand' => 'PT DIKTA',
    'type' => 'SFP Transceiver',
  ),
  203 => 
  array (
    'kode_spek' => 'B2WS02VU0104',
    'registration_number' => 'B2WS02VU010400221',
    'serial_number' => 'B2WS02VU010400221',
    'name' => 'Panel Antenna 10.5Ghz Wibas Terminal Station',
    'brand' => 'Intracom',
    'type' => 'Antenna',
  ),
  204 => 
  array (
    'kode_spek' => 'B2WS02A70204',
    'registration_number' => 'B2WS02A7020401864',
    'serial_number' => 'B2WS02A7020401864',
    'name' => 'Psu For Evolution X1 Satelite Router',
    'brand' => 'iDirect',
    'type' => 'Power Supply',
  ),
  205 => 
  array (
    'kode_spek' => 'B2WS02VU0103',
    'registration_number' => 'B2WS02VU010300416',
    'serial_number' => 'B2WS02VU010300416',
    'name' => 'Full Outdoor Wibas Terminal Station, 10.5Ghz Fdd, 2Xgbe ( Pn - )',
    'brand' => 'Intracom',
    'type' => 'Radio',
  ),
  206 => 
  array (
    'kode_spek' => 'B2WS020O0116',
    'registration_number' => 'B2WS020O011605888',
    'serial_number' => 'B2WS020O011605888',
    'name' => 'Remote Hx50-Idu- C-Band Hughes',
    'brand' => 'Hughes',
    'type' => 'Satellite Remote',
  ),
  207 => 
  array (
    'kode_spek' => 'B2WS02A70203',
    'registration_number' => 'B2WS02A7020300022',
    'serial_number' => 'B2WS02A7020300022',
    'name' => 'Evolution X1 Satellite Router',
    'brand' => 'iDirect',
    'type' => 'Router',
  ),
  208 => 
  array (
    'kode_spek' => 'B2WS010Q010T',
    'registration_number' => 'B2WS010Q010T04932',
    'serial_number' => 'B2WS010Q010T04932',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  209 => 
  array (
    'kode_spek' => 'B2WS020O011X',
    'registration_number' => 'B2WS020O011X00798',
    'serial_number' => 'B2WS020O011X00798',
    'name' => 'Buc Full Cband Rf-Cb-2W-Jptr ( Pn - Rf-Cb-2W-Jptr )',
    'brand' => 'PT DIKTA',
    'type' => 'BUC/RFU',
  ),
  210 => 
  array (
    'kode_spek' => 'B2WS0100010I',
    'registration_number' => 'B2WS0100010I04558',
    'serial_number' => 'B2WS0100010I04558',
    'name' => 'Lnb,C-Band Ls Extd Band ( Pn - 1024573-0001 )',
    'brand' => 'PT DIKTA',
    'type' => 'LNB/LNA',
  ),
  211 => 
  array (
    'kode_spek' => 'B2WS010Q010T',
    'registration_number' => 'B2WS010Q010TA0171',
    'serial_number' => 'B2WS010Q010TA0171',
    'name' => 'Feed Horn Prodelin ( Pn - )',
    'brand' => 'Prodelin',
    'type' => 'Feed Horn',
  ),
  212 => 
  array (
    'kode_spek' => 'B2WS02A70203',
    'registration_number' => 'B2WS02A7020301185',
    'serial_number' => 'B2WS02A7020301185',
    'name' => 'Evolution X1 Satellite Router',
    'brand' => 'iDirect',
    'type' => 'Router',
  ),
  213 => 
  array (
    'kode_spek' => 'B2WS020O0110',
    'registration_number' => 'B2WS020O011009119',
    'serial_number' => 'B2WS020O011009119',
    'name' => 'Psu Remote 7000 Series -Idu- C-Band Hughes ( Pn - 1031105-0001 )',
    'brand' => 'Hughes',
    'type' => 'Power Supply',
  ),
  214 => 
  array (
    'kode_spek' => 'B2WS02VU0105',
    'registration_number' => 'B2WS02VU010500541',
    'serial_number' => 'B2WS02VU010500541',
    'name' => 'Psu/Poe Wibas',
    'brand' => 'Intracom',
    'type' => 'Power Supply',
  ),
  215 => 
  array (
    'kode_spek' => 'B2WN0100041P',
    'registration_number' => 'B2WN0100041P00194',
    'serial_number' => 'B2WN0100041P00194',
    'name' => 'Enconn Eco Power 130 T Poe Over Coax Transmitter ( Pn - Eco-Power130T )',
    'brand' => 'Enconn',
    'type' => 'PoE Injector',
  ),
  216 => 
  array (
    'kode_spek' => 'B2WS02A70204',
    'registration_number' => 'B2WS02A7020403626',
    'serial_number' => 'B2WS02A7020403626',
    'name' => 'Psu For Evolution X1 Satelite Router',
    'brand' => 'iDirect',
    'type' => 'Power Supply',
  ),
  217 => 
  array (
    'kode_spek' => 'B2WS020O0116',
    'registration_number' => 'B2WS020O011604730',
    'serial_number' => 'B2WS020O011604730',
    'name' => 'Remote Hx50-Idu- C-Band Hughes',
    'brand' => 'Hughes',
    'type' => 'Satellite Remote',
  ),
  218 => 
  array (
    'kode_spek' => 'B2WN0100041Q',
    'registration_number' => 'B2WN0100041Q00190',
    'serial_number' => 'B2WN0100041Q00190',
    'name' => 'Enconn Eco Power 130 R Poe Over Coax Receiver ( Pn - ) ( Pn - Eco-Power130R )',
    'brand' => 'Enconn',
    'type' => 'PoE Injector',
  ),
  219 => 
  array (
    'kode_spek' => 'B2WS020O0116',
    'registration_number' => 'B2WS020O011600675',
    'serial_number' => 'B2WS020O011600675',
    'name' => 'Remote Hx50-Idu- C-Band Hughes',
    'brand' => 'Hughes',
    'type' => 'Satellite Remote',
  ),
  220 => 
  array (
    'kode_spek' => 'B2WS020O0110',
    'registration_number' => 'B2WS020O011007849',
    'serial_number' => 'B2WS020O011007849',
    'name' => 'Psu Remote 7000 Series -Idu- C-Band Hughes ( Pn - 1031105-0001 )',
    'brand' => 'Hughes',
    'type' => 'Power Supply',
  ),
  221 => 
  array (
    'kode_spek' => 'B2WS030O0105',
    'registration_number' => 'B2WS030O010502576',
    'serial_number' => 'B2WS030O010502576',
    'name' => 'Rf,Odu,Tigris,Ext-C 2W',
    'brand' => 'Tigris',
    'type' => 'BUC/RFU',
  ),
  222 => 
  array (
    'kode_spek' => 'B2WS0200037M',
    'registration_number' => 'B2WS0200037MA0782',
    'serial_number' => 'B2WS0200037MA0782',
    'name' => 'Full C-Band 2W Buc (Njt8102Wf)',
    'brand' => 'NJRC',
    'type' => 'BUC/RFU',
  ),
  223 => 
  array (
    'kode_spek' => 'B2WS0100010I',
    'registration_number' => 'B2WS0100010I11702',
    'serial_number' => 'B2WS0100010I11702',
    'name' => 'Lnb,C-Band Ls Extd Band ( Pn - 1024573-0001 )',
    'brand' => 'PT DIKTA',
    'type' => 'LNB/LNA',
  ),
  224 => 
  array (
    'kode_spek' => 'B2WSBKA0139M',
    'registration_number' => 'B2WSBKA0139MA0865',
    'serial_number' => 'B2WSBKA0139MA0865',
    'name' => 'Adapter 12V, 1.5A For Bivocom',
    'brand' => 'Bivocom',
    'type' => 'Power Supply',
  ),
  225 => 
  array (
    'kode_spek' => 'B2WS0200137M',
    'registration_number' => 'B2WS0200137MA0089',
    'serial_number' => 'B2WS0200137MA0089',
    'name' => 'Bivocom Industrial 4G Router 5 Ports, Dual Sim, Wifi, Dual Modul',
    'brand' => 'Bivocom',
    'type' => 'Router',
  ),
  226 => 
  array (
    'kode_spek' => 'B2WS01AA0102',
    'registration_number' => 'B2WS01AA010200406',
    'serial_number' => 'B2WS01AA010200406',
    'name' => 'Antenna Asc Signal (Raven) 1.8 M',
    'brand' => 'ASC Signal',
    'type' => 'Antenna',
  ),
  227 => 
  array (
    'kode_spek' => 'B2WS010Q0101',
    'registration_number' => 'B2WS010Q010110195',
    'serial_number' => 'B2WS010Q010110195',
    'name' => 'Antenna/Reflector 1.8 Prodelin',
    'brand' => 'Prodelin',
    'type' => 'Antenna',
  ),
  228 => 
  array (
    'kode_spek' => 'B2WS010Q0101',
    'registration_number' => 'B2WS010Q010112789',
    'serial_number' => 'B2WS010Q010112789',
    'name' => 'Antenna/Reflector 1.8 Prodelin',
    'brand' => 'Prodelin',
    'type' => 'Antenna',
  ),
  229 => 
  array (
    'kode_spek' => 'B2WS01VE0105',
    'registration_number' => 'B2WS01VE010501198',
    'serial_number' => 'B2WS01VE010501198',
    'name' => 'Antenna 1,8 Skyware',
    'brand' => 'Skyware',
    'type' => 'Antenna',
  ),
  230 => 
  array (
    'kode_spek' => 'B2WS010Q0101',
    'registration_number' => 'B2WS010Q010109664',
    'serial_number' => 'B2WS010Q010109664',
    'name' => 'Antenna/Reflector 1.8 Prodelin',
    'brand' => 'Prodelin',
    'type' => 'Antenna',
  ),
  231 => 
  array (
    'kode_spek' => 'B2WS010Q0101',
    'registration_number' => 'B2WS010Q010103760',
    'serial_number' => 'B2WS010Q010103760',
    'name' => 'Antenna/Reflector 1.8 Prodelin',
    'brand' => 'Prodelin',
    'type' => 'Antenna',
  ),
  232 => 
  array (
    'kode_spek' => 'B2WS010Q0101',
    'registration_number' => 'B2WS010Q010108863',
    'serial_number' => 'B2WS010Q010108863',
    'name' => 'Antenna/Reflector 1.8 Prodelin',
    'brand' => 'Prodelin',
    'type' => 'Antenna',
  ),
  233 => 
  array (
    'kode_spek' => 'B2WS010Q0101',
    'registration_number' => 'B2WS010Q010111972',
    'serial_number' => 'B2WS010Q010111972',
    'name' => 'Antenna/Reflector 1.8 Prodelin',
    'brand' => 'Prodelin',
    'type' => 'Antenna',
  ),
  234 => 
  array (
    'kode_spek' => 'B2WS01AA0102',
    'registration_number' => 'B2WS01AA010200347',
    'serial_number' => 'B2WS01AA010200347',
    'name' => 'Antenna Asc Signal (Raven) 1.8 M',
    'brand' => 'ASC Signal',
    'type' => 'Antenna',
  ),
  235 => 
  array (
    'kode_spek' => 'B2WS01000103',
    'registration_number' => 'B2WS0100010302749',
    'serial_number' => 'B2WS0100010302749',
    'name' => 'Canister 1.8 Diameter 4 Inchi',
    'brand' => 'PT DIKTA',
    'type' => 'Canister',
  ),
  236 => 
  array (
    'kode_spek' => 'B2WS01000103',
    'registration_number' => 'B2WS0100010312579',
    'serial_number' => 'B2WS0100010312579',
    'name' => 'Canister 1.8 Diameter 4 Inchi',
    'brand' => 'PT DIKTA',
    'type' => 'Canister',
  ),
  237 => 
  array (
    'kode_spek' => 'B2WS01000103',
    'registration_number' => 'B2WS0100010307710',
    'serial_number' => 'B2WS0100010307710',
    'name' => 'Canister 1.8 Diameter 4 Inchi',
    'brand' => 'PT DIKTA',
    'type' => 'Canister',
  ),
  238 => 
  array (
    'kode_spek' => 'B2WS01000103',
    'registration_number' => 'B2WS0100010311579',
    'serial_number' => 'B2WS0100010311579',
    'name' => 'Canister 1.8 Diameter 4 Inchi',
    'brand' => 'PT DIKTA',
    'type' => 'Canister',
  ),
  239 => 
  array (
    'kode_spek' => 'B2WS0100012O',
    'registration_number' => 'B2WS0100012O00731',
    'serial_number' => 'B2WS0100012O00731',
    'name' => 'Canister Skyware 5 Inch ( Pn - )',
    'brand' => 'Skyware',
    'type' => 'Canister',
  ),
  240 => 
  array (
    'kode_spek' => 'B2WS01000103',
    'registration_number' => 'B2WS0100010306325',
    'serial_number' => 'B2WS0100010306325',
    'name' => 'Canister 1.8 Diameter 4 Inchi',
    'brand' => 'PT DIKTA',
    'type' => 'Canister',
  ),
  241 => 
  array (
    'kode_spek' => 'B2WS01000103',
    'registration_number' => 'B2WS0100010307662',
    'serial_number' => 'B2WS0100010307662',
    'name' => 'Canister 1.8 Diameter 4 Inchi',
    'brand' => 'PT DIKTA',
    'type' => 'Canister',
  ),
  242 => 
  array (
    'kode_spek' => 'B2WS01000103',
    'registration_number' => 'B2WS0100010310749',
    'serial_number' => 'B2WS0100010310749',
    'name' => 'Canister 1.8 Diameter 4 Inchi',
    'brand' => 'PT DIKTA',
    'type' => 'Canister',
  ),
);

        foreach ($devices as $d) {
            $device = Device::updateOrCreate(
                ['registration_number' => $d['registration_number']],
                [
                    'kode_spek' => $d['kode_spek'],
                    'serial_number' => $d['serial_number'],
                    'name' => $d['name'],
                    'brand' => $d['brand'],
                    'type' => $d['type'],
                    'stock_status' => 'tersedia_hb_dikta',
                    'current_location' => 'Gudang HB Dikta',
                    'condition' => 'baru',
                    'activity' => 'Spare Dikta',
                    'notes' => 'Stok HB Dikta - Kode Spek: ' . $d['kode_spek'],
                ]
            );

            if ($device->wasRecentlyCreated) {
                DeviceHistory::create([
                    'device_id' => $device->id,
                    'actor_id' => $actorId,
                    'action' => 'Registrasi Stok HB Dikta',
                    'previous_status' => null,
                    'new_status' => 'tersedia_hb_dikta',
                    'from_location' => 'Data Awal Stok Gudang',
                    'to_location' => 'Gudang HB Dikta',
                    'notes' => 'Pendaftaran perangkat stok HB Dikta (Kode Spek: ' . $d['kode_spek'] . ')',
                ]);
            }
        }
    }
}
