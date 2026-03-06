-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 04, 2025 at 03:05 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `erp_ie`
--

-- --------------------------------------------------------

--
-- Table structure for table `actionbacklogs`
--

CREATE TABLE `actionbacklogs` (
  `id` bigint UNSIGNED NOT NULL,
  `controller_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `function_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_time` datetime NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_codes`
--

CREATE TABLE `activity_codes` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `desc_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `desc_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_codes`
--

INSERT INTO `activity_codes` (`id`, `code`, `desc_ar`, `desc_en`) VALUES
(1, '111', 'زراعة الحبوب والمحاصيل ( فيما عدا الارز ) والبقوليات والحبوب الزيتية', 'Cultivation of grains and crops (except for rice), legumes and oilseeds'),
(2, '112', 'زراعة الارز', 'Cultivation of rice'),
(3, '113', 'زراعة الخضر ؤالبطيخ والجذور والدرنات', 'Growing vegetables, melons, roots and tubers'),
(4, '114', 'زراعة قصب السكر', 'Cultivation of sugar cane'),
(5, '115', 'زراعة التبغ', 'Tobacco cultivation'),
(6, '116', 'زراعة المحاصيل ذات الالياف', 'Growing fiber crops'),
(7, '119', 'زراعة المحاصيل غير المعمرة الاخرى', 'Cultivation of other non-perennial crops'),
(8, '121', 'زراعة الكروم', 'the cultivation of grapevines.'),
(9, '122', 'زراعة الفواكه الاستوائية وشبه الاستوائية', 'Growing tropical and subtropical fruits'),
(10, '123', 'زراعة الموالح', 'Cultivation of citrus fruits'),
(11, '124', 'زراعة الفواكة ذات النواة والناتجة عن انواع النخيل', 'Cultivation of fruit with Date kernel and from palm trees'),
(12, '125', 'زراعة أشجار وشجيرات الفواكهة والمكسرات الاخرى', 'Plant fruit trees and shrubs and other nuts'),
(13, '126', 'زراعة الفواكهة الزيتية', 'Growing oil fruits'),
(14, '127', 'زراعة المحاصيل التى تستخرج منها المشروبات', 'Cultivation of the crops from which drinks are extracted'),
(15, '128', 'زراعة محاصيل التوابل والعطريات والادوية والعقاقير الصيدلانية', 'Cultivation of spice crops, aromatics, medicine and pharmaceutical drugs'),
(16, '129', 'زراعة المحاصيل المعمرة الاخرى', 'Cultivation of other perennial crops'),
(17, '130', 'تكاثر المحاصيل', 'Crop breeding'),
(18, '141', 'تربية الماشية والجواميس', 'Breeding of cattle and buffalo'),
(19, '142', 'تربية الخيول و الفرس', 'Breeding of horses and mare'),
(20, '143', 'تربية الجمال والهجن', 'Breeding of camels'),
(21, '144', 'تربية الخراف والماعز', 'Breeding sheep and goats'),
(22, '145', 'تربية الحنازير', 'Breeding of Pig'),
(23, '146', 'تربية الدواجن', 'Poultry farming'),
(24, '149', 'تربية الحيوانات الاخرى', 'Breeding other animals'),
(25, '150', 'التربية المختلطة', 'Mixed education'),
(26, '161', 'الانشطة الداعمة لانتاج المحاصيل', 'Support activities for crop production'),
(27, '162', 'الانشطة الداعمة لانتاج الحيوانات', 'Activities in support of animal production'),
(28, '163', 'الانشطة اللاحقة لحصاد المحاصيل', 'Post-harvest activities'),
(29, '164', 'تجهيز الحبوب للتكاثر', 'Preparing grains for reproduction'),
(30, '170', 'الصيد ونصب الاشراك وانشطة الخدمات ذات الصلة', 'Hunting, erection, and related service activities'),
(31, '210', 'رعاية الغابات والانشطة المتصلة بالغابات', 'Forest care and forest-related activities'),
(32, '220', 'قطع الاخشاب', 'Wood cutting'),
(33, '230', 'تجميع المنتجات غير الخشبية بالغابات', 'Assembling non-wood forest products'),
(34, '240', 'الخدمات الداعمة للغابات', 'Forest support services'),
(35, '311', 'الصيد البحرى', 'Fishing'),
(36, '312', 'الصيد النهرى', 'River fishing'),
(37, '321', 'المزارع البحرية', 'Marine farms'),
(38, '322', 'المزارع النهرية', 'River farms'),
(39, '411', 'كسب عمل', 'Earn a job'),
(40, '412', 'ايرادات من مرتبات جهات حكوميه', 'Income from government agencies salaries'),
(41, '413', 'إيرادات ومرتبات من قطاع الاعمال العام', 'Income and salaries from the public business sector'),
(42, '414', 'ايرادات ومرتبات من قطاع خاص', 'Income and salaries from the private sector'),
(43, '415', 'ايرادات ومرتبات من جهات غير خاضعه', 'Income and salary from non-subject entities'),
(44, '416', 'تفتيش ودمغة', 'Inspection and sting'),
(45, '441', 'ايرادالاراضي الزراعيه', 'Income of agricultural lands'),
(46, '442', 'ايراد العقارات المبنيه', 'Revenue from constructed real estate'),
(47, '444', 'ايراد الانشطه العقاريه', 'Income of real estate activities'),
(48, '451', 'دمغة مأمورية', 'Errand stamp'),
(49, '461', 'ايرادات غير الممولين', 'Revenue from non-funders'),
(50, '462', 'إيراد رؤوس الأموال المنقولة', 'Revenue of transferred capital'),
(51, '463', 'ايرادات محصله من الخارج', 'Income earned from abroad'),
(52, '464', 'فئات اخري/ ايرادات اخري متنوعه', 'Other categories / miscellaneous other income'),
(53, '471', 'ايرادات أسواق حرة', 'Free market revenue'),
(54, '472', 'ايرادات مناطق حره', 'Free Zones revenue'),
(55, '510', 'تعدين الفحم الصلب', 'Hard coal mining'),
(56, '520', 'تعدين الليجنيت', 'Lignite mining'),
(57, '610', 'استخرج البترول الخام', 'Extract the crude oil'),
(58, '620', 'استخرج االغاز الطبيعى', 'Extract natural gas'),
(59, '710', 'تعدين الحديد الخام', 'Iron ore mining'),
(60, '721', 'تعدين اليورانيوم والثوريوم الخام', 'Uranium and raw thorium mining'),
(61, '729', 'تعدين المعادن الخام الاخرى غير الحديدية', 'Mining other non-ferrous metals'),
(62, '810', 'استغلال المحاجر لاستخراج الاحجار والرمال والطفل', 'Quarrying to extract stones, sand and shale'),
(63, '891', 'استخراج المعادن الكيميائية والاسمدة', 'Chemical minerals and fertilizer extraction'),
(64, '892', 'استخراج الخث', 'Peat extraction'),
(65, '893', 'استخراج الملح', 'Salt extraction'),
(66, '899', 'الانشطة الاخرى للتعدين واستغلال المحاجر غير مصنفة فى موضع اخر', 'Other mining and quarrying activities are not elsewhere classified'),
(67, '910', 'انشطة الخدمات الداعمة لاعمال استخراج البترول والغاز الطبيعى', 'Service activities in support of oil and natural gas extraction'),
(68, '990', 'انشطة الخدمات الداعمة للاعمال الاخرى للتعدين واستغلال المحاجر', 'Service activities in support of other mining and quarrying activities'),
(69, '1010', 'تصنيع وحفظ اللحوم', 'Meat processing and preservation'),
(70, '1020', 'تصنيع وحفظ الاسماك والقشريات والرخويات', 'Manufacture and preservation of fish, crustaceans and mollusks'),
(71, '1030', 'تصنيع وحفظ الفواكهة و الخضراوات', 'Manufacturing and preserving fruits and vegetables'),
(72, '1040', 'تصنيع الزيوت و الدهون الحيوانية والنباتية', 'Manufacture of vegetable and animal oils and fats'),
(73, '1050', 'تصنيع منتجات الالبان', 'Dairy products manufacturing'),
(74, '1061', 'تصنيع منتجات طواحين الحبوب', 'Manufacture of grain mill products'),
(75, '1062', 'تصنيع منتجات النشاء ومنتجات النشاء', 'Manufacture of starch and starch products'),
(76, '1071', 'تصنيع منتجات المخابز', 'Manufacturing bakery products'),
(77, '1072', 'صناعة السكر', 'Sugar industry'),
(78, '1073', 'تصنيع الكاكاو والشيكولاتة والحلويات السكرية', 'Manufacture of cocoa, chocolate and sugar confectionery'),
(79, '1074', 'تصنيع المعكرونة وشرائطها والكسكسى والمنتجات النشوية المماثلة', 'Manufacturing pasta, strips, couscous and similar starchy products'),
(80, '1075', 'صناعة الوجبات و الاغذية الجاهزة', 'Meals and ready-made food industry'),
(81, '1079', 'صناعة المنتجات الاخرى غير مصنفة فى موضع اخر', 'Manufacture of other products not classified elsewhere'),
(82, '1080', 'صناعة الاغذية الحيوانية المجهزة', 'Prepared animal food industry'),
(83, '1101', 'تقطير المشروبات الروحية وتكريرها وخلطها', 'Spirits distilled, refined and mixed'),
(84, '1102', 'صناعة النبيذ', 'Winemaking'),
(85, '1103', 'صناعة المشروبات الكحولية المشتقة من المولت وصنع المولت', 'The manufacture of alcoholic drinks derived from the molten and the manufacture of molten'),
(86, '1104', 'صناعة المشروبات المرطبة وانتاج المياة المعدنية', 'Manufacturing soft drinks and producing mineral water'),
(87, '1200', 'صناعة منتجات التبغ', 'Manufacture of tobacco products'),
(88, '1311', 'تجهيز وغزل الياف المنسوجات', 'Processing and spinning of textile fibers'),
(89, '1312', 'نسج المنسوجات', 'Textile weave'),
(90, '1313', 'صناعة المنسوجات', 'The textile industry'),
(91, '1391', 'صناعة الاقمشة التريكو والكروشية', 'Manufacture of knitted and crocheted fabrics'),
(92, '1392', 'صناعة مستلزمات المنسوجات الجاهزة باستثناء ملابس الزينة', 'Manufacture of ready-made textile accessories, except garment wear'),
(93, '1393', 'صناعة السجاد والبطاطين', 'Carpet and blanket industry'),
(94, '1394', 'صناعة الحبال والحبال الغليظة والمذدوجة والشباك', 'Manufacture of ropes, thick and double ropes and nets'),
(95, '1399', 'صناعة المنسوجات الاخرى غير مصنفة فى موضع اخر', 'Other textile industry not elsewhere classified'),
(96, '1410', 'صناعة الملابس ذات الزينة باستثناء الفراء', 'Manufacture of garment with the exception of fur'),
(97, '1420', 'صناعة مستلزمات الفراء', 'Fur accessories industry'),
(98, '1430', 'صناعة ملابس الزينة بالتريكو والكروشية', 'The manufacture of clothing, knitted and crocheted'),
(99, '1511', 'دبغ وتجهيز الجلود والحشوات وصباغة الفراء', 'Tanning and processing of leather, fillings and dyeing of fur'),
(100, '1512', 'صناعة حفائب الامتعة وحقائب اليد وما شابهة ذلك الى جانب السروج واطقم الجياد', 'Luggage, handbags and similar industries, along with saddles and horse sets'),
(101, '1520', 'صناعة الاحذية', 'Shoe manufacturing'),
(102, '1610', 'نشر الخشب وسحجة', 'Sawing wood and abrasion'),
(103, '1621', 'الصفائح من قشرة الخشب والالواح ذات الاساس الخشبى', 'Sheets made of wood veneer and wood-based panels'),
(104, '1622', 'صناعة مستلزمات النجارة المعدة للابنية والمنشات', 'Manufacture of carpentry accessories intended for buildings and installations'),
(105, '1623', 'صناعة الصناديق الخشبية', 'Wooden boxes industry'),
(106, '1629', 'صناعة الاخشاب والمنتجات الخشبية والفلين باستثناء الاثاث وصناعة الاصناف المنتجة من القش والصفائح', 'Manufacture of wood, wood products and cork, except furniture, and manufacture of articles produced from straw and sheets'),
(107, '1701', 'صناعة عجائن الورق و الورق المقوى ( الكارتون )', 'Paper and carvatard pulp industry'),
(108, '1702', 'صناعة الورق والورق المقوى المموج والصناديق المصنوعة من الورق والورق المقوى', 'Manufacture of corrugated paper and paperboard and boxes made of paper and paperboard'),
(109, '1709', 'صناعة اصناف اخرى من الورق والورق المقوى', 'Manufacture of other articles of paper and paperboard'),
(110, '1811', 'الطباعة', 'printing'),
(111, '1812', 'انشطة الخدمات المرتبطة بالطباعة', 'Printing service activities'),
(112, '1820', 'استنساخ وسائل الاعلام المسجلة', 'Clone recorded media'),
(113, '1910', 'صناعة منتجات افران الكوك', 'Coke oven products industry'),
(114, '1920', 'المنتجات النفطية المكررة', 'Refined petroleum products'),
(115, '2011', 'المواد الكيميائية الاساسية', 'Basic chemicals'),
(116, '2012', 'صناعة الاسمدة والمركبات الازوتية', 'Manufacture of fertilizers and nitrogen compounds'),
(117, '2013', 'صناعة اللادائن فى اشكالها الاولية والمطاط الصناعى', 'Plastics industry in its primary forms and synthetic rubber'),
(118, '2021', 'صناعة مبيدات الافات والمنتجات الكيميائية الزراعية الاخرى', 'Pesticide industry and other agricultural chemical products'),
(119, '2022', 'صناعة الدهانات والورنيش والطلاء المماثلة واحبار الطباعة والمصطكات', 'Manufacture of paints, varnishes, and similar coatings, printing inks and molds'),
(120, '2023', 'صناعة الصابون والمطهرات ومستحضرات التنظيف والتلميع والعطور ومستحضرات التجميل', 'Manufacture of soap, disinfectants, cleaning and polishing preparations, perfumes and cosmetics'),
(121, '2029', 'صناعة المنتجات الكيميائية الاخرى غير المصنفة فى موضع اخر', 'Manufacture of other chemical products not classified elsewhere'),
(122, '2030', 'صناعة الالياف الصناعية', 'Industrial fiber industry'),
(123, '2100', 'صناعة المستحضرات الصيدلانية والكيميائية الدواءية والمنتجات النباتية', 'Manufacture of pharmaceutical, chemical, and plant products'),
(124, '2211', 'صناعة الاطارات والانابيب المطاطية وتجديد الاسطح الخارجية للاطارات المطاطية واعادة بنائها', 'Manufacture of rubber tires and tubes, renewing and rebuilding the outer surfaces of rubber tires'),
(125, '2219', 'صناعة المنتجات المطاطية الاخرى', 'Manufacture of other rubber products'),
(126, '2220', 'صناعة منتجات اللادائن', 'Plastics industry'),
(127, '2310', 'صناعة الزجاج ومنتجاتة', 'Glass and its products industry'),
(128, '2391', 'صناعة المنتجات المقاومة للانصهار', 'Manufacture of fusion products'),
(129, '2392', 'صناعة مواد الطفلة الخاصة بالبناء', 'Manufacture of Shale products for Building'),
(130, '2393', 'صناعة منتجات البرسولين والسيراميك الاخرى', 'Manufacture of other Porcelain and ceramic products'),
(131, '2394', 'صناعة الاسمنت والجير والجص', 'Cement, lime and plaster industry'),
(132, '2395', 'صناعة منتجات الخرسانة والاسمنت والجص', 'Manufacture of concrete products, cement and plaster'),
(133, '2396', 'قطع وتشكيل واتمام تجهيز الاحجار', 'Cutting, forming and completing the stone processing'),
(134, '2399', 'صناعة منتجات المعادن الافلزية الاخرى غير المصنفة فى مواضع اخرى', 'Manufacture of non-metallic minerals products not classified elsewhere'),
(135, '2410', 'صناعة الحديد والصلب القاعديين', 'The industry of basic iron and steel'),
(136, '2420', 'صناعة الفلزات الثمينة وغير الحديدية القاعدية', 'Manufacture of precious and non-ferrous basic metals'),
(137, '2431', 'سبك الحديدوالصلب', 'Iron and steel casting'),
(138, '2432', 'سبك المعادن غير الحديدية', 'Non-ferrous metal casting'),
(139, '2511', 'صناعة المنتجات المعدنية الانشائية', 'Structural metal products industry'),
(140, '2512', 'صناعة الصهاريج والخزانات و الحاويات المعدنية', 'Industry of tanks and metal containers'),
(141, '2513', 'مولدات بخار الماء باستثناء مراجل التدفئةالمركزية بالمياة الساخنة', 'Water vapor generators except for central heating boilers in hot water'),
(142, '2520', 'صناعة الاسلحة والذخائر', 'Arms and ammunition industry'),
(143, '2591', 'تشكيل المعادن بالطرق والكبس و السبك والدلفنة ومعالجة مساحيق المعادن', 'Forming metals by hammering, pressing, casting, rolling, and treatment of metal powders'),
(144, '2592', 'معالجة وطلى المعادن', 'Metal processing and coating'),
(145, '2593', 'صناعة ادوات القطع والعدد اليدوية والادوات المعدنية العامة', 'Manufacture of cutting tools, hand tools and general metal tools'),
(146, '2599', 'صناعة منتجات المعادن المشكلة الاخرى غير المصنفة فى مواضع اخرى', 'Manufacture of other fabricated metal products not classified elsewhere'),
(147, '2610', 'صناعة المكونات والالواح الاليكترونية', 'Electronic components and panels industry'),
(148, '2620', 'صناعة الحاسبات الاليكترونية و الاجهزة المتصلة بة', 'The manufacture of electronic computers and related devices'),
(149, '2630', 'صناعة اجهزة الاتصالات', 'Communications equipment industry'),
(150, '2640', 'صناعة الاجهزة الاليكترونية', 'Electronic devices industry'),
(151, '2651', 'صناعة اجهزة القياس والاختبار و الملاحة والتحكم', 'Manufacturing measuring, testing, navigation and control devices'),
(152, '2652', 'صناعة الساعات والمنبهات', 'Watch and alarm clock industry'),
(153, '2660', 'صناعة الاجهزة الاشعاعية والطبية والعلاجية اللاليكترونية', 'Radiation, medical and therapeutic electronic devices industry'),
(154, '2670', 'صناعة المعدات البصرية واجهزة التصوير', 'Optical equipment and imaging equipment industry'),
(155, '2680', 'صناعة الوسائل الناقلة البصرية والمغنطيسية', 'Optical and magnetic conveyor industry'),
(156, '2710', 'صناعة المحركات والمولدات والمحولات الكهربائية واجهزة ولوحات التحكم فى توزيع الكهرباء', 'Manufacture of motors, generators, electrical transformers, devices and control panels for electricity distribution'),
(157, '2720', 'صناعة البطاريات الجافة والمختزنة', 'Manufacture of dry and stored batteries'),
(158, '2731', 'صناعة كابلات الالياف الصناعية', 'Industrial fiber cable industry'),
(159, '2732', 'صناعة الاسلاك الكهربائية والاليكترونية الاخرى والكابلات', 'Other electrical and electronic wires and cables'),
(160, '2733', 'صناعة اجهزة الاسلاك', 'Wire devices industry'),
(161, '2740', 'صناعة اجهزة الانارة الكهربائية', 'Electrical lighting devices industry'),
(162, '2750', 'صناعة الاجهزة المنزلية', 'Home appliances industry'),
(163, '2790', 'صناعة الاجهزة الكهربائية الاخرى', 'Other electrical appliances industry'),
(164, '2811', 'صناعة المولدات والمحركات باسقثناء الطائرات والمركبات ومحركات الموتيسيكلات', 'Manufacture of generators and engines, with the exception of aircraft, vehicles and motorcycles'),
(165, '2812', 'صناعة اجهزة الطاقة السائلة', 'Liquid power devices industry'),
(166, '2813', 'صناعة المضخات والضواغط والاشرطة والصمامات الاخرى', 'Manufacture of pumps, compressors, tapes and other valves'),
(167, '2814', 'صناعة التروس والحاملات واجهزة القيادة', 'Gears, carriers and driving devices industry'),
(168, '2815', 'صناعة الافران و الاتونات ومحرقاتها', 'Manufacture of furnaces, furnaces and their incinerators'),
(169, '2816', 'صناعة المصاعد والمعدات الازمة لها', 'The elevators and equipment needed for it'),
(170, '2817', 'صناعة الاجهزة والمعدات المكتبية ( باستثناء الحاسبات الاليكترونية ومستلزماتها )', 'Manufacture of office equipment and equipment (excluding electronic computers and their accessories)'),
(171, '2818', 'صناعة المعدات اليدوية لتوجية الطاقة', 'Manufacture of manual power steering equipment'),
(172, '2819', 'صناعة المعدات الاخرى ذات الاغراض المتنوعة', 'Other equipment industry of various purposes'),
(173, '2821', 'صناعة المعدات الزراعية والخاصة بالغابات', 'Agricultural and forestry equipment industry'),
(174, '2822', 'صناعة معدات واجهزة تشكيل المعادن', 'Manufacture of equipment and machinery for forming metals'),
(175, '2823', 'صناعة معدات المعادن', 'Metal equipment industry'),
(176, '2824', 'صناعة معدات المناجم و المحاجر والبناء', 'Mining and quarrying and building equipment industry'),
(177, '2825', 'صناعة معدات الصناعات الغذائية والمشرؤبات والتبغ', 'Manufacture of food, beverage and tobacco industries equipment'),
(178, '2826', 'صناعة معدات الملابس الجاهزة وهدوات الزينة وانتاج الجلود', 'Manufacture of ready-made clothes, accessories, and leather production'),
(179, '2829', 'صناعة المعدات الاخرى ذات الاغراض الخاصة', 'Manufacture of other special-purpose equipment'),
(180, '2910', 'صناعة المركبات ذات المحركات', 'Manufacture of motor vehicles'),
(181, '2920', 'صناعة هياكل المركبات ذات المحركات وصناعة المقطورات ونصف المقطورات', 'Manufacture of motor vehicle bodies and the manufacture of trailers and semi-trailers'),
(182, '2930', 'صناعة مستلزمات وقطع غيار المركبات ذات المحركات', 'Manufacture of accessories and spare parts for motor vehicles'),
(183, '3011', 'بناء هياكل السفن و الطوافات', 'Building ship hulls and rafts'),
(184, '3012', 'صناعة قوارب النزهة والقوارب الرياضية', 'Manufacture of pleasure boats and sport boats'),
(185, '3020', 'صناعة قاطرات السكك الحديدية والمعدات الدارجة على السكك الحديدية', 'Railroad locomotives and rolling stock industry'),
(186, '3030', 'صناعة المركبات الهوائية والفضائية', 'Air and spacecraft industry'),
(187, '3040', 'صناعة المركبات العسكرية الحربية', 'Manufacture of military military vehicles'),
(188, '3091', 'صناعة', 'Industry'),
(189, '3092', 'صناعة الدراجات العادية ومركبات العجزة', 'Manufacture of ordinary bicycles and infirm vehicles'),
(190, '3099', 'صناعة معدات النقل الاخرى غير المصنفة فى مواضع اخرى', 'Other transportation equipment industry not classified elsewhere'),
(191, '3100', 'صناعة الاثاث', 'Furniture Industry'),
(192, '3211', 'صناعة المجوهرات والاصناف المتصلة بها', 'Manufacture of jewelry and related items'),
(193, '3212', 'صناعة المجوهرات المقلدة والاصناف المتصلة بها', 'Manufacture of imitation jewelry and related items'),
(194, '3220', 'صناعة الالات الموسيقية', 'Musical instrument industry'),
(195, '3230', 'صناعة المنتجات الرياضية', 'Sports products industry'),
(196, '3240', 'صناعة الالعاب واللعب', 'Make games and play'),
(197, '3250', 'صناعة المعدات والادوات الطبية والخاصة بالاسنان', 'Manufacturing of dental and medical equipment and tools'),
(198, '3290', 'الصناعات الاخرى غير المصنفة فى مواضع اخرى', 'Other industries not classified elsewhere'),
(199, '3311', 'اصلاح المنتجات المعدنية المصنعة', 'Repair of manufactured metal products'),
(200, '3312', 'اصلاح الالات', 'Machinery repair'),
(201, '3313', 'اصلاح الاجهزة الاليكترونية و البصرية', 'Repair of electronic and optical devices'),
(202, '3314', 'اصلاح الاجهزة الاليكترونية', 'Electronic devices repair'),
(203, '3315', 'اصلاح اجهزة النقل باستثناء المركبات ذات المحركات', 'Repair of transport devices, except for motor vehicles'),
(204, '3319', 'اصلاح الاجهزة الاخرى', 'Repair other devices'),
(205, '3320', 'تركيب المعدات و الاجهزة الصناعية', 'Installation of industrial equipment and devices'),
(206, '3510', 'المولدات الكهربائية ومحولات وموزعات الكهرباء', 'Electric generators, transformers and power distributors'),
(207, '3520', 'صناعة غاز الاستصباح وتوزيع انواع الوقود الغازية عن طريق مواسير رئيسية', 'Manufacture of sulfur gas and distribution of gaseous fuels by means of main pipes'),
(208, '3530', 'توريد البخار واجهزة تكييف الهواء', 'Steam supply and air conditioning'),
(209, '3600', 'تجميع ومعالجة وتوريد المياة', 'Water collection, treatment and supply'),
(210, '3700', 'المجارى', 'Sewer'),
(211, '3811', 'تجميع المخلفات غير الخطرة', 'Collection of non-hazardous waste'),
(212, '3812', 'تجميع المخلفات الخطرة', 'Collection of hazardous waste'),
(213, '3821', 'معالجة والتصرف فى المخلفات غير الخطرة', 'Treatment and disposal of non-hazardous waste'),
(214, '3822', 'معالجة والتصرف فى المخلفات الخطرة', 'Treatment and disposal of hazardous waste'),
(215, '3830', 'معالجة المواد', 'Material handling'),
(216, '3900', 'انشطة وخدمات اعادة التدوير والتصرف فى النفايات الاخرى', 'Recycling activities and services and the disposal of other waste'),
(217, '4100', 'انشاءات المبانى', 'Building constructions'),
(218, '4210', 'الانشاءات الخاصة بالطرق والسكك الحديدية', 'Road and railway constructions'),
(219, '4220', 'الانشاءات الخاصة بالمشاريع ذات المنفعة العامة', 'Construction for projects of public benefit'),
(220, '4290', 'الانشاءات الخاصة بالمشاريع الهندسية المدنية الاخرى', 'Construction for other civil engineering projects'),
(221, '4311', 'ازالة المنشات', 'Remove the installations'),
(222, '4312', 'اعداد المواقع', 'Preparing sites'),
(223, '4321', 'التركيبات الكهربائية', 'Electrical installations'),
(224, '4322', 'التركيبات الخاصة بالسباكة والتدفئة ومكيفات الهواء', 'Plumbing, heating and air-conditioning installations'),
(225, '4329', 'التركيبات الانشائية الاخرى', 'Other structural installations'),
(226, '4330', 'استكمال وتشطيب المبانى', 'Completion and finishing of buildings'),
(227, '4390', 'انشطة الانشاءات المتخصصة الاخرى', 'Other specialized construction activities'),
(228, '4510', 'بيع المركبات ذات المحركات', 'Sale of motor vehicles'),
(229, '4520', 'صيانة واصلاح المركبات ذات المحركات', 'Maintenance and repair of motor vehicles'),
(230, '4530', 'بيع قطع غيار ومستلزمات المركبات ذات المحركات', 'Sale of motor vehicle parts and accessories'),
(231, '4540', 'بيع وصيانة واصلاح الدراجات النارية وقطع الغيار والمستلزمات الخاصة بها', 'Sale, maintenance and repair of motorcycles, parts and accessories thereof'),
(232, '4610', 'تجارة الجملة على اساس عقد او نظير رسم', 'Wholesale trade on the basis of a contract or a fee'),
(233, '4620', 'تجارة الجملة الخاصة بالمواد الاولية الزراعية والحيوانات الحية', 'Wholesale trade in agricultural raw materials and live animals'),
(234, '4630', 'تجارة الجملة الخاصة بالاغذية والمشرؤبات والتبغ', 'Wholesale trade of food, beverages and tobacco'),
(235, '4641', 'تجارة الجملة الخاصة بالملابس والاقمشة والاحذية', 'Wholesale trade of clothes, fabrics and shoes'),
(236, '4649', 'تجارة الجملة الخاصة بالادوات المنزلية الاخرة', 'Wholesale trade for other household appliances'),
(237, '4651', 'تجارة الجملة الخاصة باجهزة الكمبيوتر ومستلزماتها وبرامج الكمبيوتر', 'Wholesale trade of computer hardware, accessories and computer software'),
(238, '4652', 'تجارة الجملة الخاصة بالاجهزة الاليكترونية واجهزة الاتصالات ومستلزماتها', 'Wholesale trade of electronic devices, communications devices and accessories'),
(239, '4653', 'تجارة الجملة الخاصة بالمعدات والالات والتوريدات الزراعية', 'Wholesale trade for agricultural equipment, machinery and supplies'),
(240, '4659', 'تجارة الجملة الخاصة بالمعدات والاجهزة الاخرى', 'Wholesale trade of equipment and other devices'),
(241, '4661', 'تجارة الجملة الخاصة بالوقود الجاف والسائل والغازى والمنتجات المرتبطة بذلك', 'Wholesale trade of dry, liquid and gaseous fuels and related products'),
(242, '4662', 'تجارة الجملة الخاصة بالمعادن والمعادن النفيسة', 'Wholesale trade in precious metals and minerals'),
(243, '4663', 'تجارة الجملة والتوريدات والاجهزة الخاصة بمواد البناء والادوات المعدنية والسباكة واجهزة التدفئة', 'Wholesale trade, supplies and equipment for building materials, hardware, plumbing and heating appliances'),
(244, '4669', 'تجارة الجملة الخاصة بالنفايات والمخلفات والمنتجات الاخرى غير المصنفة فى مواضع اخرى', 'Wholesale trade for waste, waste and other products not classified elsewhere'),
(245, '4690', 'تجارة الجملة غير المتخصصة', 'Non-specialized wholesale trade'),
(246, '4711', 'البيع بالتجزئة بالمتاجر غير المتخصصة للاغذية والمشروبات اوالتبغ', 'Retail sale in non-specialized stores of food, beverages or tobacco'),
(247, '4719', 'انواع البيع بالتجزئةالاخرى بالمتاجر غير المتخصصة', 'Other retail types in non-specialized stores'),
(248, '4721', 'البيع بالتجزئة بالمتاجر المتخصصة للاغذية', 'Retail sale in specialized food stores'),
(249, '4722', 'البيع بالتجزئة بالمتاجر المتخصصة للمشروبات', 'Retail sale in specialized stores for drinks'),
(250, '4723', 'البيع بالتجزئة بالمتاجر المتخصصة لمنتجات التبغ', 'Retail sale in specialized stores of tobacco products'),
(251, '4730', 'البيع بالتجزئة بالاماكن المتخصصة لوقود المركبات', 'Retail sale of specialized vehicles for fuel'),
(252, '4741', 'البيع بالتجزئة بالمتاجر المتخصصة فى اجهزة الحاسب الالى ومستلزماتة وبرامج الكمبيوتر واجهزة الاتصالات', 'Retail sale in stores specialized in computer hardware, accessories, computer software, and communications equipment'),
(253, '4751', 'البيع بالتجزئة بالمتاجر المتخصصة فى الملابس', 'Retail sale in clothing stores'),
(254, '4752', 'البيع بالتجزئة بالمتاجر المتخصصة للادوات المعدنية و الطلاء والزجاج', 'Retail sale in specialized stores of hardware, paint and glass'),
(255, '4753', 'البيع بالتجزئة بالمتاجر المتخصصة للسجاد والبطاطين واغطية الحوائط والارضيات', 'Retail sale in specialized stores of carpets, blankets, wall and floor coverings'),
(256, '4759', 'البيع بالتجزئة بالمتاجر المتخصصة للاجهزة الكهربائية المنزلية والاثاث واجخزة الانارة والادوات المنزلية الاخرى', 'Retail sale in specialized stores of household electrical appliances, furniture, lighting equipment and other household appliances'),
(257, '4761', 'البيع بالتجزئة بالمتاجر المتخصصة للكتب والصحف والادوات المكتبية', 'Retail sale in specialized stores of books, newspapers, and stationery'),
(258, '4762', 'البيع بالتجزئة بالمتاجر المتخصصة للتسجيلاتالموسشيقية والمرئية', 'Retail sale in specialized stores of music and video recordings'),
(259, '4763', 'البيع بالتجزئة بالمتاجر المتخصصة للادوات الرياضية', 'Retail sale in specialized stores of sports equipment'),
(260, '4764', 'البيع بالتجزئة بالمتاجر المتخصصة للالعاب واللعب', 'Retail sale in specialized games and toys stores'),
(261, '4771', 'البيع بالتجزئة بالمتاجر المتخصصة للاحذية والملابس والمنتجات الجلدية', 'Retail sale in specialized stores of shoes, clothing and leather products'),
(262, '4772', 'البيع بالتجزئة بالمتاجر المتخصصة للمنتجات والعقاقير الصيدلانية والطبية وادوات الزينة والمنتجات التجميلية', 'Retail sale in specialized stores of pharmaceutical, medical and pharmaceutical products, ornamental and cosmetic products'),
(263, '4773', 'البيع بالتجزئة بالمتاجر المتخصصة للمنتجات الجديدة الاخرى', 'Retail sale in specialized stores of other new products'),
(264, '4774', 'البيع بالتجزئة للمنتجات المستعملة', 'Retail sale of used products'),
(265, '4781', 'البيع بالتجزئة من خلال الاكشاك والاسواق للمواد الغذائية والمشروبات الخفيفة ومنتجات التبغ', 'Retail sale through kiosks and markets of food, soft drinks and tobacco products'),
(266, '4782', 'البيع بالتجزئة من خلال الاكشاك والاسواق للملابس والاقمشة والاحذية', 'Retail sale through kiosks and markets of clothes, fabrics and shoes'),
(267, '4789', 'البيع بالتجزئة من خلال الاكشاك للمنتجات الاخرى', 'Retail sale via stalls of other products'),
(268, '4742', 'البيع بالتجزئة بالمتاجر المتخصصة فى الاجهزة السمعية والبصرية', 'Retail sale in stores specialized in audio-visual equipment'),
(269, '4791', 'البيع بالتجزئة عبر طلبات البريد او من خلال الانترنت', 'Retail sale via mail requests or through the Internet'),
(270, '4799', 'انواع البيع بالتجزئةالاخرى التى لاتتم بالنتاجر او الاكشاك او الاسواق', 'Other types of retail sales that do not take place in stores, kiosks or markets'),
(271, '4911', 'النقل الداخلى للركاب', 'Inland passenger transportation'),
(272, '4912', 'الشحن عن طريق السكك الحديدية', 'Shipping by rail'),
(273, '4921', 'نقل الركاب البرى خارج وداخل المدن', 'Transporting land passengers outside and inside cities'),
(274, '4922', 'انواع نقل الركاب البرية الاخرى', 'Other types of passenger transport by land'),
(275, '4923', 'النقل البرى للبضائع عن طريق الحافلات', 'Land transportation of goods by bus'),
(276, '4930', 'النقل عبر خطوط الانابيب', 'Pipeline transportation'),
(277, '5011', 'نقل الركاب البحرى والساحلى', 'Transportation of marine and coastal passengers'),
(278, '5012', 'نقل البضائع البحرى والساحلى', 'Marine and coastal cargo transportation'),
(279, '5021', 'النقل الداخلى المائى للركاب', 'Inland passenger water transport'),
(280, '5022', 'النقل الداخلى المائى للبضائع', 'Inland water transport of goods'),
(281, '5110', 'النقل الجوى للركاب', 'Air transport of passengers'),
(282, '5120', 'النقل الجوى للبضائع', 'Air freight transport'),
(283, '5210', 'الاحتفاظ والتخزين', 'Keep and store'),
(284, '5221', 'انشطة الخدمات المتصلة بالنقل البرى', 'Service activities related to road transport'),
(285, '5222', 'انشطة الخدمات الطارئة المتصلة بالنقل البحرى', 'Emergency service activities related to maritime transport'),
(286, '5223', 'انشطة الخدمات الطارئة المتصلة بالنقل الجوى', 'Emergency service activities related to air transport'),
(287, '5224', 'مناولة البضائع', 'Cargo handling'),
(288, '5229', 'الانشطة الاخرى الداعمة للنقل', 'Other activities in support of the transfer'),
(289, '5310', 'انشطة البريد', 'Mail activities'),
(290, '5320', 'انشطة تسليم الطرود', 'Parcel delivery activities'),
(291, '5510', 'انشطة التسكين قصيرة المدة', 'Short-term placement activities (rental - housing'),
(292, '5520', 'اراضى المخيمات ومواقف مركبات التنزهة والقاطرات', 'Campgrounds, parking lots, and locomotives'),
(293, '5590', 'انواع التسكين الاخرى', 'Other types of placement'),
(294, '5610', 'انشطة خدمات المطاعم وتوصيل الطعام بالوسائل المتحركة', 'Restaurant service and food delivery activities by mobile means'),
(295, '5621', 'تقديم الطعام بالمناسبات', 'Event catering'),
(296, '5629', 'انشطة خدمات تقديم الطعام الاخرى', 'Other catering services activities'),
(297, '5630', 'انشطة خدمات تقديم المشروبات الخفيفة', 'Light beverage service activities'),
(298, '5811', 'نشر الكتب', 'Publishing books'),
(299, '5812', 'نشر الدليل وقوائم العناوين', 'Publish the directory and address lists'),
(300, '5813', 'نشر الصحف والمجلات والدوريات', 'Publishing newspapers, magazines and periodicals'),
(301, '5819', 'انشطة النشر الاخرى', 'Other publishing activities'),
(302, '5820', 'نشر برامج الحاسب الالى', 'Computer Software Publishing'),
(303, '5911', 'انشطة انتاج الافلام السينمائية والفيديو وبرامج التليفزيون', 'Film, video and television program production activities'),
(304, '5912', 'الانشطة الاحقة لانتاج الافلام السينمائية والفيديو وبرامج التليفزيون', 'Subsequent activities for the production of movies, videos and television programs'),
(305, '5913', 'انشطة توزيع الافلام السينمائية والفيديو وبرامج التليفزيون', 'Motion picture, video and television program distribution activities'),
(306, '5914', 'انشطة عرض الافلام السينمائية', 'Film screening activities'),
(307, '5920', 'انشطة انتاج ونشر التسجيلات الصوتية والموسيقية', 'Production and publishing of sound and music recordings'),
(308, '6010', 'البث عبر محطات الراديو', 'Broadcasting over radio stations'),
(309, '6020', 'انشطة اعداد برامج التليفزيون وبثها', 'Television program preparation and broadcast activities'),
(310, '6110', 'انشطة الاتصالات السلكية', 'Wired telecommunications activities'),
(311, '6120', 'انشطة الاتصالات اللاسلكية', 'Wireless communication activities'),
(312, '6130', 'انشطة الاتصالات عبر الاقمار الصناعية', 'Satellite communication activities'),
(313, '6190', 'انشطة الاتصالات السلكية و اللاسلكية الاخرى', 'Other telecommunications activities'),
(314, '6201', 'انشطة اعداد برامج الحاسب الالى', 'Computer program preparation activities'),
(315, '6202', 'الخبرة الاستشارية فى مجال الحاسب الالى وانشطة ادارة التسهيلات المتصلة بمجالات الحاسب الالى', 'Computer consultancy experience and facilities management activities related to computer fields'),
(316, '6209', 'الانشطة الاخرى المتصلة بتكنولوجيا المعلومات وخدمات الحاسب الالى', 'Other activities related to information technology and computer services'),
(317, '6311', 'معالجة البيانات والاستضافة والانشطة المتصلة بذلك', 'Data processing, hosting and related activities'),
(318, '6312', 'البوابات الاليكترونية', 'Electronic portals'),
(319, '6391', 'انشطة الوكالات الصحفية', 'Activities of press agencies'),
(320, '6399', 'الانشطة الاخرى لخدمات تقديم المعلومات غير المصنفة فى مواقع اخرى', 'Other activities for information services that are not classified in other locations'),
(321, '6411', 'المصارف المركزية', 'Central banks'),
(322, '6419', 'الوساطات المالية الاخرى', 'Other financial intermediaries'),
(323, '6420', 'انشطة الشركات القابضة', 'Activities of holding companies'),
(324, '6430', 'انشطةالائتمان وتوفير الاعتمادات والكيانات المالية المشابهة', 'Credit activities, provision of credits, and similar financial entities'),
(325, '6491', 'التاجير المالى', 'Financial leasing'),
(326, '6492', 'اشكال القروض الممنوحة الاخرى', 'Other forms of loans granted'),
(327, '6499', 'انشطة الخدمات المالية الاخرى , باستثناء التامين وانشطة توفير الاعتمادات للمعاشات التقاعدية غير المصنفة فى مواقع اخرى', 'Other financial services activities, with the exception of insurance and credit provision activities for pensions not classified in other locations'),
(328, '6511', 'التامين على الحياة', 'life insurance'),
(329, '6512', 'التامين على غير الحياة', 'Non-life insurance'),
(330, '6520', 'اعادة التامين', 're Insurance'),
(331, '6530', 'توفير الاعتمادات للمعاشات التقاعدية', 'Providing credits for pensions'),
(332, '6611', 'ادارة الاسواق المالية', 'Financial markets management'),
(333, '6612', 'الامن وسمسرة عقود السلع', 'Security and commodity contracts brokerage'),
(334, '6619', 'الانشطة المساعدة للخدمات المالية', 'Auxiliary activities for financial services'),
(335, '6621', 'تقدير المخاطر والتلفيات', 'Risk and damage assessment'),
(336, '6622', 'انشطة وكلاء التامين والسمسرة', 'Activities of insurance and brokerage agents'),
(337, '6629', 'الانشطة الاخرى المساعدة للتامين وتوفير الاعتمادات للمعاشات التقاعدية', 'Other activities auxiliary to insurance and provision for pensions'),
(338, '6630', 'انشطة ادارة الاعتمادات المالية', 'Financial credit management activities'),
(339, '6810', 'الانشطة العقارية فى الممتلكات المملوكة او المؤجرة', 'Real estate activities with own or leased property'),
(340, '6820', 'الانشطة العقارية على اساس عقد او نظير رسم', 'Real estate activities on the basis of a contract or a fee'),
(341, '6910', 'الانشطة القانونية', 'Legal activities'),
(342, '6920', 'الانشطة المحاسبية والمراجعة ومسك الدفاتر والاستشارات الضرائبية', 'Accounting, auditing, bookkeeping and tax advice activities'),
(343, '7010', 'انشطة المكاتب الرئيسية', 'The main office activities'),
(344, '7020', 'الانشطة الاستشارية الخاصة بالادارة', 'Management consultancy activities'),
(345, '7110', 'الانشطة المعمارية والهندسية و الاستشارات الفنية المتصلة بذلك', 'Architectural and engineering activities and related technical consulting'),
(346, '7120', 'الاختبارات والتحليلات الفنية', 'Technical tests and analyzes'),
(347, '7210', 'البحث والتطوير التجريبى فى مجال العلوم الطبيعية والهندسية', 'Research and experimental development in the field of natural and engineering sciences'),
(348, '7220', 'البحث والتطوير التجريبى فى مجال العلوم الاجتماعية والانسانية', 'Experimental research and development in the field of social and human sciences'),
(349, '7310', 'الاعلان', 'Advertising'),
(350, '7320', 'دراسات السوق واستطلاعات الراى العام', 'Market studies and public opinion polls'),
(351, '7410', 'انشطة التصميمات المتخصصة', 'Specialized design activities'),
(352, '7420', 'الانشطة الفوتوغرافية', 'Photographic activities'),
(353, '7490', 'الانشطة الاخرى التخصصية والعلمية والفنية غير المصنفة فى مواضع اخرى', 'Other specialized, scientific and artistic activities not classified elsewhere'),
(354, '7500', 'الانشطة البيطرية', 'Veterinary activities'),
(355, '7710', 'تاجيرواستئجار المركبات ذات المحركات', 'Renting motor vehicles'),
(356, '7721', 'تاجير واستئجار منتجات والادوات الرياضية والترفيهية', 'Renting and renting sports and leisure products and tools'),
(357, '7722', 'تاجير شرائط واسطوانات الفيديو', 'Rental of video tapes and CDs'),
(358, '7729', 'تاجيرواستئجار المنتجات الشخصية والمنزلية الاخرى', 'Renting and renting other personal and household products'),
(359, '7730', 'تاجير واستئجار الاجهزة والمعدات المادية الاخرى', 'Renting and leasing of other physical devices and equipment'),
(360, '7740', 'استئجار اشكال الملكية الفكرية والمنتجات المشابهة باستثناء اعمال حقوق المؤلف', 'Rent forms of intellectual property and similar products, except for copyright works'),
(361, '7810', 'انشطة وكالات التوظيف والتعيين', 'Activities of recruitment and appointment agencies'),
(362, '7820', 'انشطة وكالات التوظيف المؤقت', 'Activities of temporary employment agencies'),
(363, '7830', 'توفير المصادر البشرية الاخرى', 'Providing other human resources'),
(364, '7911', 'خدمات وكالات السياحة', 'Tourism agency services'),
(365, '7912', 'انشطة المرشدين السياحيين', 'Activities of tour guides'),
(366, '7990', 'انواع الحجوزات الاخرى والانشط المتصلة بذلك', 'Other types of reservations and related activities'),
(367, '8010', 'انشطة الامن الخاص', 'Private security activities'),
(368, '8020', 'انشطة خدمات انظمة الامن', 'Security systems services activities'),
(369, '8030', 'انشطة التحرى', 'Investigation activities'),
(370, '8110', 'انشطة دعم التسهيلات المشتركة', 'Support activities for joint facilities'),
(371, '8121', 'النظافة العامة للمبانى', 'General cleaning of buildings'),
(372, '8129', 'انشطة تنظيف المبانى والمنشات الصناعية الاخرى', 'Building cleaning activities and other industrial facilities'),
(373, '8130', 'انشطة خدمات رعاية وصيانة الحدائق', 'Gardening services and maintenance activities'),
(374, '8211', 'انشطة خدمات الدعم المكتبية المشتركة', 'Joint office support services activities'),
(375, '8219', 'النسخ وتجهيز المستندات وانشطة خدمات الدعم المكتبية المتخصصة الاخرى', 'Photocopying, document processing and other specialized office support services activities'),
(376, '8220', 'خدمات مراكز الاستعلامات', 'Information center services'),
(377, '8230', 'تنظيم المؤتمرات والمعارض التجارية', 'Organizing trade conferences and exhibitions'),
(378, '8291', 'انشطة وكالات التحصيل ومكاتب الاقراض', 'Activities of collection agencies and lending offices'),
(379, '8292', 'انشطة التغليف', 'Packaging activities'),
(380, '8299', 'انشطة خدمات الدعم الاخرى الخاصة بالاعمال التجارية غير المصنفة فى مواضع اخرى', 'Other support services activities that are not classified in other locations'),
(381, '8411', 'انشطة الادارات العامة', 'Public administration activities'),
(382, '8412', 'تنظيم انشطة تقديم العناية الصحية والتعليم والخدمات التثقيفية والخدمات الاجتماعية الاخرى باستثناء الضمان الاجتماعى', 'Organizing activities to provide health care, education, educational services and other social services, with the exception of social security'),
(383, '8413', 'تنظيم والمساهمة فى عمليات الاعمال التجارية الفعالة', 'Organize and contribute to effective business operations'),
(384, '8421', 'الشئون الخارجية', 'Foreign affairs'),
(385, '8422', 'انشطة الدفاع', 'Defense activities'),
(386, '8423', 'انشطة الامن والنظام العام', 'Security and public order activities'),
(387, '8430', 'انشطة التامين الاجتماعى الاجبارى', 'Compulsory social insurance activities'),
(388, '8510', 'التعليم الابتدائى وقبل الابتدائى', 'Primary and pre-primary education'),
(389, '8521', 'التعليم الثانوى العام', 'General secondary education'),
(390, '8522', 'التعليم الثانوى الفنى والهنى', 'Technical and vocational secondary education'),
(391, '8530', 'التعليم العالى', 'Higher Education'),
(392, '8541', 'التعليم الرياضى والتاهيلى', 'Sports and rehabilitation education'),
(393, '8542', 'التعليم الثقافى', 'Cultural education'),
(394, '8549', 'انواع التعليم الاخرى غير المصنفة فى مواضع اخرى', 'Other types of education not classified elsewhere'),
(395, '8550', 'الانشطة الداعمة للتعليم', 'Activities in support of education'),
(396, '8610', 'انشطة المستشفيات', 'Hospital activities'),
(397, '8620', 'الانشطة المتصلة بالطب وطب الاسنان', 'Activities related to medicine and dentistry'),
(398, '8690', 'الانشطة الاخرى المتصلة بصحة الانسان', 'Other activities related to human health'),
(399, '8710', 'تسهيلات العناية بالتمريض بالمصحات', 'Nursing facilities for sanatoriums'),
(400, '8720', 'تسهيلات العناية بالتمريض بالمصحات الخاصة بذوى الاحتياجات الخاصة والامراض العقلية والانتهاكات البدنية', 'Nursing care facilities for special needs clinics, mental illnesses and physical abuse'),
(401, '8730', 'تسهيلات العناية بالمصحات الخاصة بالعجائز والمقعدين', 'Spa facilities for the elderly and disabled'),
(402, '8790', 'تسهيلات العناية الاخرى بالمصحات', 'Other spa care facilities'),
(403, '8810', 'انشطة الاعمال الاجتماعية للعجزة والمقعدين التى تتم بدون اقامة', 'Social work activities for the infirm and disabled that take place without accommodation'),
(404, '8890', 'انشطة الاعمال الاجتماعية الاخرى التى تتم بدون اقامة', 'Other social business activities that take place without residence'),
(405, '9000', 'انشطة الفن الابداعى والترفية', 'Creative and recreational art activities'),
(406, '9101', 'انشطة المكتبات والارشيف', 'Library and archive activities'),
(407, '9102', 'انشطة المتاحف وعمليات ترميم المواقع والمبانى التاريخية', 'Museum activities and restoration of historic sites and buildings'),
(408, '9103', 'حدائق النباتات والحيوانات وانشطة الحياة البرية الطبيعية', 'Botanical and zoological gardens and natural wildlife activities'),
(409, '9200', 'انشطة المراهنات والعاب القمار', 'Betting activities and gambling'),
(410, '9311', 'توفير التسهيلات الرياضية', 'Providing sports facilities'),
(411, '9312', 'انشطة النوادى الرياضية', 'Sports club activities'),
(412, '9319', 'الانشطة الرياضية الاخرى', 'Other sports activities'),
(413, '9321', 'الانشطة الترفيهية والعروض التى تتم بالمتنزهات', 'Recreational activities and performances in parks'),
(414, '9329', 'الانشطة الترفيهية والمسلية الاخرى غير المصنفة فى مواضع اخرى', 'Other leisure and entertainment activities not classified elsewhere'),
(415, '9411', 'انشطة المؤسسات التجارية وارباب العمل ومنظمات العضوية المهنية', 'The activities of commercial enterprises, employers and professional membership organizations'),
(416, '9412', 'انشطة منظمات العضوية المهنية', 'Activities of professional membership organizations'),
(417, '9420', 'انشطة النقابات التجارية', 'Trade union activities'),
(418, '9491', 'انشطة المنظمات الدينية', 'Activities of religious organizations'),
(419, '9492', 'انشطة المنظمات السياسية', 'Activities of political organizations'),
(420, '9499', 'انشطة المنظمات الاخرى ذات العضوية غير المصنفة فى مواضع اخرى', 'Activities of other membership organizations not classified elsewhere'),
(421, '9511', 'اصلاح الحاسبات الالية ومستلزماتها', 'Computer repair and accessories'),
(422, '9512', 'اصلاح اجهزة الاتصالات', 'Communication equipment repair'),
(423, '9521', 'اصلاح الاجهزة الاليكترونية', 'Electronic devices repair'),
(424, '9522', 'اصلاح الادوات والاجهزة المنزلية ومعدات العناية بالحدائق', 'Repair of tools, household appliances, and garden care equipment'),
(425, '9523', 'اصلاح الاحذية والمنتجات الجلدية', 'Shoe and leather products repair'),
(426, '9524', 'اصلاح الاثاث والمستلزمات المنزلية', 'Repair of furniture and household items'),
(427, '9529', 'اصلاح المنتجات المنزلية والشخصية الاخرى', 'Repair of other household and personal products'),
(428, '9601', 'غسيل المنسوجات والمنتجات الفرائية وتنظيفها تنظيفا جافا', 'Wash and clean textile and fur products'),
(429, '9602', 'تصفيف الشعر وانواع التجميل الاخرى', 'Hair styling and other cosmetics'),
(430, '9603', 'انشطة الجنائز وما يتصل بها', 'Funeral and related activities'),
(431, '9609', 'انشطة الخدمات الشخصية الاخرى غير المصنفة فى مواضع اخرى', 'Other personal services activities not classified elsewhere'),
(432, '9700', 'انشطة توظيف العمالة المنزلية', 'Home employment activities'),
(433, '9810', 'انشطة انتاج المنتجات والخدمات غير المتميزة الخاصة بالاجهزة المنزلية للاستخدام الشخصى', 'Activities of producing unearthed products and services for home appliances for personal use'),
(434, '9820', 'انشطة انتاج المنتجات والخدمات غير المتميزة الخاصة بالاجهزة المنزلية للاستخدام الشخصى', 'Activities of producing unearthed products and services for home appliances for personal use'),
(435, '9900', 'انشطة المنظمات والهيئات غير الاقليمية', 'Activities of non-regional organizations and bodies');

-- --------------------------------------------------------

--
-- Table structure for table `addon_categories`
--

CREATE TABLE `addon_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addon_categories`
--

INSERT INTO `addon_categories` (`id`, `name_ar`, `name_en`, `description_ar`, `description_en`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'فئة 1', 'addon cat 1', 'وصف', 'descc', '2024-12-30 12:18:37', '2025-01-15 11:57:15', NULL),
(2, 'فئة 2', 'addon cat 2', NULL, NULL, '2024-12-30 12:21:23', '2024-12-30 12:21:23', NULL),
(3, 'new', 'new', NULL, NULL, '2025-01-05 11:59:24', '2025-01-05 11:59:47', '2025-01-05 11:59:47'),
(4, 'new', 'new', NULL, NULL, '2025-01-15 11:57:32', '2025-01-15 11:57:49', '2025-01-15 11:57:49'),
(5, 'a', 'a', 'وصف', 'description', '2025-01-19 10:42:00', '2025-01-19 10:42:04', '2025-01-19 10:42:04'),
(6, 'a', 'a', 'وصف', 'desc', '2025-01-20 13:39:54', '2025-01-21 09:11:50', '2025-01-21 09:11:50'),
(7, 'a', 'a', 'وصف', 'desc', '2025-01-20 13:39:54', '2025-01-21 09:11:54', '2025-01-21 09:11:54'),
(8, 'Uta Lewis', 'Samantha Middleton', 'Saepe cillum cillum', 'Eius in corrupti in', '2025-01-20 14:29:58', '2025-01-20 14:29:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `advances`
--

CREATE TABLE `advances` (
  `id` bigint UNSIGNED NOT NULL,
  `request_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `approval_date` date NOT NULL,
  `starting_date` date NOT NULL,
  `ending_date` date NOT NULL,
  `amount_per_month` decimal(15,2) NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `advance_requests`
--

CREATE TABLE `advance_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `advance_setting_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('0','1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '0 -> rejected, 1 -> Pending, 2 -> approved',
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `advance_settings`
--

CREATE TABLE `advance_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `min_salary` decimal(15,2) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `months` decimal(5,2) NOT NULL,
  `amount_per_month` decimal(15,2) NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `advance_settings`
--

INSERT INTO `advance_settings` (`id`, `min_salary`, `amount`, `months`, `amount_per_month`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 8500.00, 16000.00, 12.00, 1335.00, 10, 10, 10, '2024-10-31 10:23:20', '2024-10-31 10:41:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `apicodes`
--

CREATE TABLE `apicodes` (
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id` bigint UNSIGNED NOT NULL,
  `code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_code_title_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_code_title_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_code_message_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_code_message_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `apicodes`
--

INSERT INTO `apicodes` (`created_at`, `updated_at`, `id`, `code`, `api_code_title_en`, `api_code_title_ar`, `api_code_message_en`, `api_code_message_ar`, `deleted_at`) VALUES
(NULL, NULL, 1, '1', 'Success', 'صحيح', 'Success Message', 'طلب صحيح', NULL),
(NULL, NULL, 2, '2', 'Failed', 'غير صحيح', 'Failed Message', 'طلب غير صحيح', NULL),
(NULL, NULL, 3, '3', 'Attention', 'انتبه', 'The new password cannot be the same as the current password', 'لا يمكن أن تكون كلمة المرور الجديدة هي نفس كلمة المرور الحالية', NULL),
(NULL, NULL, 4, '4', 'Success', 'تم بنجاح', 'User logged out', 'يجب تسجيل الدخول أولا', NULL),
(NULL, NULL, 5, '5', 'Failed', 'غير صحيح', 'Please , Login to access this route', 'التوكين غير صحيح', NULL),
(NULL, NULL, 6, '6', 'Category can\'t delete', 'التصنيف لا يمكن حذفه', 'Category cannot be deleted as it has associated product', 'التصنيف لا يمكن حذفه لانه مرتبط بالمنتج', NULL),
(NULL, NULL, 7, '7', 'Failed', 'حدث خطأ', 'You cannot update the opening balance because there are transactions on this product in this store after the creation date.', 'لا يمكنك تعديل الرصيد الافتتاحي لأن هناك معاملات على المنتج في هذا المتجر بعد هذا التاريخ.', NULL),
(NULL, NULL, 8, '8', 'NotExist', 'لا يوجد', 'Data is not exist.', 'البيانات غير موجودة', NULL),
(NULL, NULL, 9, '9', 'AlreadyExist', 'موجود بالفعل ', 'Data is already exist.', 'البيانات موجودة بالفعل', NULL),
(NULL, NULL, 10, '10', 'NoChange', 'لا يوجد تغير', 'Data is not changed.', 'لا يوجد تحديث', NULL),
(NULL, NULL, 11, '11', 'CouponNotValid', 'قسيمة خصم غير صالحة', 'Coupon is not valid', 'القسمية غير صالحة للاستخدام', NULL),
(NULL, NULL, 12, '12', 'Success', 'صحيح', 'User logged in successfully.', 'تم تسجيل الدخول بنجاح', NULL),
(NULL, NULL, 13, '13', 'Failed', 'غير صحيح', 'ُEmail or password is incorrect.', 'البريد الإلكتروني أو كلمة المرور غير صحيحة', NULL),
(NULL, NULL, 14, '14', 'Failed', 'غير صحيح', 'ُFailed to generate token.', 'حدث خطأ يرجى المحاولة مرة أخرى', NULL),
(NULL, NULL, 15, '15', 'Success', 'صحيح', 'ُPassword changed successfully.', 'تم تغيير كلمة المرور بنجاح', NULL),
(NULL, NULL, 16, '16', 'Success', 'صحيح', 'ُUser logged out successfully.', 'تم تسجيل الخروج بنجاح', NULL),
(NULL, NULL, 17, '17', 'NotFound', 'لا يوجد بيانات', 'ُClient data not found.', 'لم يتم العثور على بيانات العميل', NULL),
(NULL, NULL, 18, '18', 'Success', 'صحيح', 'ُClient data retrieved successfully.', 'تم عرض تفاصيل العميل بنجاح', NULL),
(NULL, NULL, 19, '19', 'Success', 'صحيح', 'ُProfile updated successfully.', 'تم تحديث البيانات بنجاح', NULL),
(NULL, NULL, 20, '20', 'NotFound', 'لا يوجد', 'ُNo ordrers found.', 'لا توجد طلبات', NULL),
(NULL, NULL, 21, '21', 'Success', 'صحيح', 'ُOrder is reorded successfully.', 'تم اعادة الطلب بنجاح', NULL),
(NULL, NULL, 22, '22', 'NotFound', 'لا يوجد', 'ُOrder not found.', 'الطلب غير موجود', NULL),
(NULL, NULL, 23, '23', 'Success', 'صحيح', 'User created successfully.', 'تم التسجيل بنجاح', NULL),
(NULL, NULL, 24, '24', 'Failed', 'حدث خطأ', 'More than one point calculation system cannot be activated', 'لا يمكن تفعيل اكثر من نظام لحساب النقاط', NULL),
(NULL, NULL, 25, '25', 'Failed', 'حدث خطأ', 'All systems are disabled and the system must be activated', 'كل الانظمه غير مفعله و يحب تفعيل نظام', NULL),
(NULL, NULL, 26, '26', 'Success', 'صحيح', 'Purchase Invoices retrieved successfully.', 'تم عرض تفاصيل فواتير المشتريات بنجاح', NULL),
(NULL, NULL, 27, '27', 'Success', 'صحيح', 'Purchase Invoice created successfully.', 'تم تخزين فاتورة المشتريات بنجاح', NULL),
(NULL, NULL, 28, '28', 'Failed', 'حدث خطأ', 'Cannot update purchase invoice linked to a store transaction', 'لا يمكن التعديل على فاتورة مشتريات مربوطة بحركة في المخزن', NULL),
(NULL, NULL, 29, '29', 'Success', 'صحيح', 'Purchase invoice (refund) updated successfully.', 'تم تعديل فاتورة المشتريات (مسترجع) بنجاح', NULL),
(NULL, NULL, 30, '30', 'Success', 'صحيح', 'Reports fetched successfully.', 'تم عرض التقارير بنجاح', NULL),
(NULL, NULL, 31, '1', 'Success', 'صحيح', 'Success Message', 'طلب صحيح', NULL),
(NULL, NULL, 32, '2', 'Failed', 'غير صحيح', 'Failed Message', 'طلب غير صحيح', NULL),
(NULL, NULL, 33, '3', 'Attention', 'انتبه', 'The new password cannot be the same as the current password', 'لا يمكن أن تكون كلمة المرور الجديدة هي نفس كلمة المرور الحالية', NULL),
(NULL, NULL, 34, '4', 'Success', 'تم بنجاح', 'User logged out', 'يجب تسجيل الدخول أولا', NULL),
(NULL, NULL, 35, '5', 'Failed', 'غير صحيح', 'Please , Login to access this route', 'التوكين غير صحيح', NULL),
(NULL, NULL, 36, '6', 'Category can\'t delete', 'التصنيف لا يمكن حذفه', 'Category cannot be deleted as it has associated product', 'التصنيف لا يمكن حذفه لانه مرتبط بالمنتج', NULL),
(NULL, NULL, 37, '7', 'Failed', 'حدث خطأ', 'You cannot update the opening balance because there are transactions on this product in this store after the creation date.', 'لا يمكنك تعديل الرصيد الافتتاحي لأن هناك معاملات على المنتج في هذا المتجر بعد هذا التاريخ.', NULL),
(NULL, NULL, 38, '8', 'NotExist', 'لا يوجد', 'Data is not exist.', 'البيانات غير موجودة', NULL),
(NULL, NULL, 39, '9', 'AlreadyExist', 'موجود بالفعل ', 'Data is already exist.', 'البيانات موجودة بالفعل', NULL),
(NULL, NULL, 40, '10', 'NoChange', 'لا يوجد تغير', 'Data is not changed.', 'لا يوجد تحديث', NULL),
(NULL, NULL, 41, '11', 'CouponNotValid', 'قسيمة خصم غير صالحة', 'Coupon is not valid', 'القسمية غير صالحة للاستخدام', NULL),
(NULL, NULL, 42, '12', 'Success', 'صحيح', 'User logged in successfully.', 'تم تسجيل الدخول بنجاح', NULL),
(NULL, NULL, 43, '13', 'Failed', 'غير صحيح', 'ُEmail or password is incorrect.', 'البريد الإلكتروني أو كلمة المرور غير صحيحة', NULL),
(NULL, NULL, 44, '14', 'Failed', 'غير صحيح', 'ُFailed to generate token.', 'حدث خطأ يرجى المحاولة مرة أخرى', NULL),
(NULL, NULL, 45, '15', 'Success', 'صحيح', 'ُPassword changed successfully.', 'تم تغيير كلمة المرور بنجاح', NULL),
(NULL, NULL, 46, '16', 'Success', 'صحيح', 'ُUser logged out successfully.', 'تم تسجيل الخروج بنجاح', NULL),
(NULL, NULL, 47, '17', 'NotFound', 'لا يوجد بيانات', 'ُClient data not found.', 'لم يتم العثور على بيانات العميل', NULL),
(NULL, NULL, 48, '18', 'Success', 'صحيح', 'ُClient data retrieved successfully.', 'تم عرض تفاصيل العميل بنجاح', NULL),
(NULL, NULL, 49, '19', 'Success', 'صحيح', 'ُProfile updated successfully.', 'تم تحديث البيانات بنجاح', NULL),
(NULL, NULL, 50, '20', 'NotFound', 'لا يوجد', 'ُNo ordrers found.', 'لا توجد طلبات', NULL),
(NULL, NULL, 51, '21', 'Success', 'صحيح', 'ُOrder is reorded successfully.', 'تم اعادة الطلب بنجاح', NULL),
(NULL, NULL, 52, '22', 'NotFound', 'لا يوجد', 'ُOrder not found.', 'الطلب غير موجود', NULL),
(NULL, NULL, 53, '23', 'Success', 'صحيح', 'User created successfully.', 'تم التسجيل بنجاح', NULL),
(NULL, NULL, 54, '24', 'Failed', 'حدث خطأ', 'More than one point calculation system cannot be activated', 'لا يمكن تفعيل اكثر من نظام لحساب النقاط', NULL),
(NULL, NULL, 55, '25', 'Failed', 'حدث خطأ', 'All systems are disabled and the system must be activated', 'كل الانظمه غير مفعله و يحب تفعيل نظام', NULL),
(NULL, NULL, 56, '26', 'Success', 'صحيح', 'Purchase Invoices retrieved successfully.', 'تم عرض تفاصيل فواتير المشتريات بنجاح', NULL),
(NULL, NULL, 57, '27', 'Success', 'صحيح', 'Purchase Invoice created successfully.', 'تم تخزين فاتورة المشتريات بنجاح', NULL),
(NULL, NULL, 58, '28', 'Failed', 'حدث خطأ', 'Cannot update purchase invoice linked to a store transaction', 'لا يمكن التعديل على فاتورة مشتريات مربوطة بحركة في المخزن', NULL),
(NULL, NULL, 59, '29', 'Success', 'صحيح', 'Purchase invoice (refund) updated successfully.', 'تم تعديل فاتورة المشتريات (مسترجع) بنجاح', NULL),
(NULL, NULL, 60, '30', 'Success', 'صحيح', 'Reports fetched successfully.', 'تم عرض التقارير بنجاح', NULL),
(NULL, NULL, 61, '31', 'Failed', 'حدث خطأ', 'Expiration date must be after the creation date.', 'تاريخ الانتهاء يجب ان يكون بعد تاريخ الانشاء', NULL),
(NULL, NULL, 62, '32', 'Success', 'صحيح', 'Gift applied successfully to specified users.', 'تم تعيين الهدية للمستخدمين المحددين', NULL),
(NULL, NULL, 63, '33', 'Success', 'صحيح', 'Gift applied successfully to users in specified branch.', 'تم تعيين الهدية للمستخدمين التابعين للفرع المحدد', NULL),
(NULL, NULL, 64, '34', 'Failed', 'عفوا', 'Order Can\'t cancel after now.', 'لا يمكن الغاء الطلب بعد الان', NULL),
(NULL, NULL, 65, '35', 'success', 'صحيح', 'Phone number verified successfully.', 'تم التأكد من رقم الهاتف بنجاح', NULL),
(NULL, NULL, 66, '1', 'Success', 'صحيح', 'Success Message', 'طلب صحيح', NULL),
(NULL, NULL, 67, '2', 'Failed', 'غير صحيح', 'Failed Message', 'طلب غير صحيح', NULL),
(NULL, NULL, 68, '3', 'Attention', 'انتبه', 'The new password cannot be the same as the current password', 'لا يمكن أن تكون كلمة المرور الجديدة هي نفس كلمة المرور الحالية', NULL),
(NULL, NULL, 69, '4', 'Success', 'تم بنجاح', 'User logged out', 'يجب تسجيل الدخول أولا', NULL),
(NULL, NULL, 70, '5', 'Failed', 'غير صحيح', 'Please , Login to access this route', 'التوكين غير صحيح', NULL),
(NULL, NULL, 71, '6', 'Category can\'t delete', 'التصنيف لا يمكن حذفه', 'Category cannot be deleted as it has associated product', 'التصنيف لا يمكن حذفه لانه مرتبط بالمنتج', NULL),
(NULL, NULL, 72, '7', 'Failed', 'حدث خطأ', 'You cannot update the opening balance because there are transactions on this product in this store after the creation date.', 'لا يمكنك تعديل الرصيد الافتتاحي لأن هناك معاملات على المنتج في هذا المتجر بعد هذا التاريخ.', NULL),
(NULL, NULL, 73, '8', 'NotExist', 'لا يوجد', 'Data is not exist.', 'البيانات غير موجودة', NULL),
(NULL, NULL, 74, '9', 'AlreadyExist', 'موجود بالفعل ', 'Data is already exist.', 'البيانات موجودة بالفعل', NULL),
(NULL, NULL, 75, '10', 'NoChange', 'لا يوجد تغير', 'Data is not changed.', 'لا يوجد تحديث', NULL),
(NULL, NULL, 76, '11', 'CouponNotValid', 'قسيمة خصم غير صالحة', 'Coupon is not valid', 'القسمية غير صالحة للاستخدام', NULL),
(NULL, NULL, 77, '12', 'Success', 'صحيح', 'User logged in successfully.', 'تم تسجيل الدخول بنجاح', NULL),
(NULL, NULL, 78, '13', 'Failed', 'غير صحيح', 'ُEmail or password is incorrect.', 'البريد الإلكتروني أو كلمة المرور غير صحيحة', NULL),
(NULL, NULL, 79, '14', 'Failed', 'غير صحيح', 'ُFailed to generate token.', 'حدث خطأ يرجى المحاولة مرة أخرى', NULL),
(NULL, NULL, 80, '15', 'Success', 'صحيح', 'ُPassword changed successfully.', 'تم تغيير كلمة المرور بنجاح', NULL),
(NULL, NULL, 81, '16', 'Success', 'صحيح', 'ُUser logged out successfully.', 'تم تسجيل الخروج بنجاح', NULL),
(NULL, NULL, 82, '17', 'NotFound', 'لا يوجد بيانات', 'ُClient data not found.', 'لم يتم العثور على بيانات العميل', NULL),
(NULL, NULL, 83, '18', 'Success', 'صحيح', 'ُClient data retrieved successfully.', 'تم عرض تفاصيل العميل بنجاح', NULL),
(NULL, NULL, 84, '19', 'Success', 'صحيح', 'ُProfile updated successfully.', 'تم تحديث البيانات بنجاح', NULL),
(NULL, NULL, 85, '20', 'NotFound', 'لا يوجد', 'ُNo ordrers found.', 'لا توجد طلبات', NULL),
(NULL, NULL, 86, '21', 'Success', 'صحيح', 'ُOrder is reorded successfully.', 'تم اعادة الطلب بنجاح', NULL),
(NULL, NULL, 87, '22', 'NotFound', 'لا يوجد', 'ُOrder not found.', 'الطلب غير موجود', NULL),
(NULL, NULL, 88, '23', 'Success', 'صحيح', 'User created successfully.', 'تم التسجيل بنجاح', NULL),
(NULL, NULL, 89, '24', 'Failed', 'حدث خطأ', 'More than one point calculation system cannot be activated', 'لا يمكن تفعيل اكثر من نظام لحساب النقاط', NULL),
(NULL, NULL, 90, '25', 'Failed', 'حدث خطأ', 'All systems are disabled and the system must be activated', 'كل الانظمه غير مفعله و يحب تفعيل نظام', NULL),
(NULL, NULL, 91, '26', 'Success', 'صحيح', 'Purchase Invoices retrieved successfully.', 'تم عرض تفاصيل فواتير المشتريات بنجاح', NULL),
(NULL, NULL, 92, '27', 'Success', 'صحيح', 'Purchase Invoice created successfully.', 'تم تخزين فاتورة المشتريات بنجاح', NULL),
(NULL, NULL, 93, '28', 'Failed', 'حدث خطأ', 'Cannot update purchase invoice linked to a store transaction', 'لا يمكن التعديل على فاتورة مشتريات مربوطة بحركة في المخزن', NULL),
(NULL, NULL, 94, '29', 'Success', 'صحيح', 'Purchase invoice (refund) updated successfully.', 'تم تعديل فاتورة المشتريات (مسترجع) بنجاح', NULL),
(NULL, NULL, 95, '30', 'Success', 'صحيح', 'Reports fetched successfully.', 'تم عرض التقارير بنجاح', NULL),
(NULL, NULL, 96, '31', 'Failed', 'حدث خطأ', 'Expiration date must be after the creation date.', 'تاريخ الانتهاء يجب ان يكون بعد تاريخ الانشاء', NULL),
(NULL, NULL, 97, '32', 'Success', 'صحيح', 'Gift applied successfully to specified users.', 'تم تعيين الهدية للمستخدمين المحددين', NULL),
(NULL, NULL, 98, '33', 'Success', 'صحيح', 'Gift applied successfully to users in specified branch.', 'تم تعيين الهدية للمستخدمين التابعين للفرع المحدد', NULL),
(NULL, NULL, 99, '34', 'Failed', 'عفوا', 'Order Can\'t cancel after now.', 'لا يمكن الغاء الطلب بعد الان', NULL),
(NULL, NULL, 100, '35', 'success', 'صحيح', 'Phone number verified successfully.', 'تم التأكد من رقم الهاتف بنجاح', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint UNSIGNED NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `address_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `latitute` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `longitute` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `country_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manager_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opening_hour` time DEFAULT NULL,
  `closing_hour` time DEFAULT NULL,
  `has_kids_area` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_delivery` tinyint(1) NOT NULL DEFAULT '0',
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `is_default` int DEFAULT '0',
  `tax_application` tinyint NOT NULL DEFAULT '0' COMMENT '0:not included tax, 1:included tax',
  `coupon_application` tinyint NOT NULL DEFAULT '0' COMMENT '0:before tax, 1:after tax',
  `tax_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `time_cancellation` int DEFAULT NULL,
  `delivery_time` int DEFAULT NULL,
  `service_fees` decimal(10,2) DEFAULT '0.00',
  `tax_apply` int DEFAULT NULL COMMENT '0:not tax apply, 1:tax apply',
  `is_active` int DEFAULT '1',
  `delivery_fees` decimal(5,2) NOT NULL DEFAULT '0.00',
  `governate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regionCity` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buildingNumber` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postalCode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `floor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `landmark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additionalInformation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name_en`, `name_ar`, `address_en`, `address_ar`, `latitute`, `longitute`, `country_id`, `phone`, `email`, `manager_name`, `opening_hour`, `closing_hour`, `has_kids_area`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `is_delivery`, `employee_id`, `is_default`, `tax_application`, `coupon_application`, `tax_percentage`, `time_cancellation`, `delivery_time`, `service_fees`, `tax_apply`, `is_active`, `delivery_fees`, `governate`, `regionCity`, `street`, `buildingNumber`, `postalCode`, `floor`, `room`, `landmark`, `additionalInformation`) VALUES
(10, 'Main branch', 'الفرع الرئيسي', 'mohandesen giza', 'المهندسين الجيزة', '30.06625186999783', '31.20141506197', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '01224303330', 'branch@branches.com', 'احمد', NULL, NULL, 1, 1, 9, NULL, '2025-01-08 07:22:55', '2025-01-29 13:34:04', NULL, 0, 3, 1, 1, 1, 30.00, 5, 20, 100.00, 1, 1, 10.00, 'egypt', 'cairo', 'sphinx street', '12', '11047', '1', '1', 'vodafone shop', 'mohandussen'),
(11, 'second branch', 'فرع ثاني', 'mohandesen', 'مهندسين', '30.061714733985077', '31.208853947599124', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '01224302220', NULL, 'احمد', NULL, NULL, 1, 1, 9, NULL, '2025-01-08 08:19:25', '2025-01-27 12:40:41', NULL, 1, 4, 0, 1, 0, 10.00, 10, 20, 50.00, 1, 1, 5.00, 'egypt', 'cairo', 'sphinx street', '14', '11047', '2', '2', 'vodafone shop', 'mohandussen'),
(12, 'third branch', 'الفرع الثالث', 'marsa matroh', 'مرسي مطروح', '31.3543445', '27.2373159', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '01224305550', 'branch@branch.com', 'first', NULL, NULL, 1, 1, 9, NULL, '2025-01-08 08:50:11', '2025-01-29 13:34:04', NULL, 1, NULL, 0, 1, 1, 15.00, 10, 20, 30.00, 1, 1, 20.00, 'egypt', 'cairo', 'sphinx street', '16', '11047', '1', '3', 'vodafone shop', 'mohandussen'),
(13, 'fourth branch', 'الفرع الرابع', 'ahram gardens', 'حدائق الاهرام', '29.9490625204979', '31.095407104294388', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '01224306660', 'n@gmail.com', 'احمد', NULL, NULL, 1, 9, 9, 9, '2025-01-13 11:12:59', '2025-02-03 14:06:10', '2025-02-03 14:06:10', 1, NULL, 0, 0, 0, 3.00, 5, 20, 20.00, 0, 1, 30.00, 'egypt', 'cairo', 'sphinx street', '18', '11047', '2', '1', 'vodafone shop', 'mohandussen');

-- --------------------------------------------------------

--
-- Table structure for table `branch_coupon`
--

CREATE TABLE `branch_coupon` (
  `id` bigint UNSIGNED NOT NULL,
  `coupon_id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_coupon`
--

INSERT INTO `branch_coupon` (`id`, `coupon_id`, `branch_id`, `created_at`, `updated_at`) VALUES
(6, 11, 10, NULL, NULL),
(7, 11, 11, NULL, NULL),
(9, 12, 10, NULL, NULL),
(10, 12, 11, NULL, NULL),
(12, 13, 10, NULL, NULL),
(13, 13, 11, NULL, NULL),
(16, 14, 10, NULL, NULL),
(17, 14, 11, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branch_discount`
--

CREATE TABLE `branch_discount` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `discount_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_discount`
--

INSERT INTO `branch_discount` (`id`, `branch_id`, `discount_id`, `created_at`, `updated_at`) VALUES
(37, 10, 24, NULL, NULL),
(38, 11, 24, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branch_menus`
--

CREATE TABLE `branch_menus` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `dish_id` bigint UNSIGNED DEFAULT NULL,
  `branch_menu_category_id` bigint UNSIGNED DEFAULT NULL,
  `is_active` int DEFAULT '0',
  `is_product` int DEFAULT '0',
  `price` decimal(8,2) DEFAULT '0.00',
  `created_by` bigint UNSIGNED NOT NULL DEFAULT '10',
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_menus`
--

INSERT INTO `branch_menus` (`id`, `branch_id`, `dish_id`, `branch_menu_category_id`, `is_active`, `is_product`, `price`, `created_by`, `modified_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(114, 10, 46, 59, 1, 0, 10.00, 9, 9, NULL, NULL, '2025-01-16 12:00:05', '2025-01-26 09:19:00'),
(115, 11, 46, 60, 1, 0, 10.00, 9, 9, NULL, NULL, '2025-01-16 12:00:05', '2025-02-03 14:14:10'),
(116, 12, 46, 61, 1, 0, 10.00, 9, 9, NULL, NULL, '2025-01-16 12:00:05', '2025-02-03 14:14:10'),
(117, 13, 46, 62, 1, 0, 10.00, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 12:00:05', '2025-02-03 14:06:10'),
(118, 10, 48, 59, 1, 0, 30.00, 1, 9, NULL, NULL, '2025-01-16 12:01:07', '2025-01-22 11:39:52'),
(119, 11, 48, 60, 1, 0, 50.00, 9, 9, NULL, NULL, '2025-01-16 12:01:07', '2025-01-23 12:43:43'),
(120, 12, 48, 61, 1, 0, 30.00, 9, 9, NULL, NULL, '2025-01-16 12:01:07', '2025-01-23 12:44:22'),
(121, 13, 48, 62, 1, 0, 30.00, 67, 9, 9, '2025-02-03 14:06:10', '2025-01-16 12:01:07', '2025-02-03 14:06:10'),
(122, 10, 54, 59, 1, 0, 50.00, 9, 9, NULL, NULL, '2025-01-16 12:22:19', '2025-01-26 09:19:00'),
(123, 11, 54, 60, 1, 0, 50.00, 9, NULL, NULL, NULL, '2025-01-16 12:22:19', '2025-01-19 10:11:21'),
(124, 12, 54, 61, 1, 0, 50.00, 9, NULL, NULL, NULL, '2025-01-16 12:22:19', '2025-01-19 10:23:24'),
(125, 13, 54, 62, 1, 0, 50.00, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 12:22:20', '2025-02-03 14:06:10'),
(126, 10, 55, 59, 1, 0, 50.00, 9, 9, NULL, NULL, '2025-01-16 12:30:21', '2025-01-26 09:19:00'),
(127, 11, 55, 60, 1, 0, 50.00, 9, 9, NULL, NULL, '2025-01-16 12:30:21', '2025-01-23 08:09:30'),
(128, 12, 55, 61, 1, 0, 50.00, 9, NULL, NULL, NULL, '2025-01-16 12:30:21', '2025-01-16 12:30:21'),
(129, 13, 55, 62, 1, 0, 50.00, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 12:30:21', '2025-02-03 14:06:10'),
(130, 10, 56, 59, 1, 0, 50.00, 9, 9, NULL, NULL, '2025-01-16 12:32:18', '2025-01-20 10:03:59'),
(131, 11, 56, 60, 1, 0, 50.00, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-19 10:11:21'),
(132, 12, 56, 61, 1, 0, 50.00, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(133, 13, 56, 62, 1, 0, 50.00, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 12:32:18', '2025-02-03 14:06:10'),
(134, 10, 57, 63, 1, 0, 50.00, 9, 9, NULL, NULL, '2025-01-16 14:29:25', '2025-01-22 11:59:16'),
(135, 11, 57, 64, 1, 0, 50.00, 9, NULL, NULL, NULL, '2025-01-16 14:29:25', '2025-01-16 14:29:25'),
(136, 12, 57, 65, 1, 0, 50.00, 9, 9, NULL, NULL, '2025-01-16 14:29:25', '2025-01-19 10:23:24'),
(137, 13, 57, 66, 1, 0, 50.00, 9, 9, 9, '2025-02-03 14:06:10', '2025-01-16 14:29:25', '2025-02-03 14:06:10'),
(138, 10, 58, 63, 1, 0, 10.00, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:52', '2025-01-19 10:51:32'),
(139, 11, 58, 64, 1, 0, 10.00, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:52', '2025-01-19 10:51:32'),
(140, 12, 58, 65, 1, 0, 10.00, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:52', '2025-01-19 10:51:32'),
(141, 13, 58, 66, 1, 0, 10.00, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:52', '2025-01-19 10:51:32'),
(142, 10, 59, 59, 1, 0, 99.00, 9, NULL, 9, '2025-01-19 10:31:08', '2025-01-19 10:09:48', '2025-01-19 10:31:08'),
(143, 11, 51, 60, 1, 0, NULL, 9, NULL, NULL, NULL, '2025-01-19 10:11:21', '2025-01-19 10:11:21'),
(144, 11, 52, 60, 1, 0, 12.00, 9, 9, NULL, NULL, '2025-01-19 10:11:21', '2025-01-22 08:34:04'),
(145, 11, 53, 60, 1, 0, 5.00, 9, NULL, NULL, NULL, '2025-01-19 10:11:21', '2025-01-19 10:11:21'),
(146, 11, 59, 60, 1, 0, 99.00, 9, NULL, 9, '2025-01-19 10:31:08', '2025-01-19 10:11:21', '2025-01-19 10:31:08'),
(147, 12, 51, 61, 1, 0, NULL, 9, NULL, NULL, NULL, '2025-01-19 10:23:24', '2025-01-19 10:23:24'),
(148, 12, 52, 61, 1, 0, 12.00, 9, NULL, NULL, NULL, '2025-01-19 10:23:24', '2025-01-21 10:55:50'),
(149, 12, 53, 61, 1, 0, 5.00, 9, NULL, NULL, NULL, '2025-01-19 10:23:24', '2025-01-19 10:23:24'),
(150, 12, 59, 61, 1, 0, 99.00, 9, NULL, 9, '2025-01-19 10:31:08', '2025-01-19 10:23:24', '2025-01-19 10:31:08'),
(151, 10, 60, 59, 1, 0, 50.00, 9, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:06:26', '2025-01-21 10:14:25'),
(152, 11, 60, 60, 1, 0, 50.00, 9, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:06:26', '2025-01-21 10:14:25'),
(153, 12, 60, 61, 1, 0, 50.00, 9, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:06:26', '2025-01-21 10:14:25'),
(154, 13, 60, 62, 1, 0, 50.00, 67, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:06:26', '2025-01-21 10:14:25'),
(155, 13, 51, 62, 1, 0, NULL, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:45', '2025-02-03 14:06:10'),
(156, 13, 52, 62, 1, 0, 12.00, 9, 9, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:45', '2025-02-03 14:06:10'),
(157, 13, 53, 62, 1, 0, 5.00, 67, NULL, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:45', '2025-02-03 14:06:10'),
(158, 10, 61, 63, 1, 0, 930.00, 1, NULL, 9, '2025-01-22 12:51:22', '2025-01-22 12:25:47', '2025-01-22 12:51:22'),
(159, 11, 61, 64, 1, 0, 930.00, 1, NULL, 9, '2025-01-22 12:51:22', '2025-01-22 12:25:47', '2025-01-22 12:51:22'),
(160, 12, 61, 65, 1, 0, 930.00, 1, NULL, 9, '2025-01-22 12:51:22', '2025-01-22 12:25:47', '2025-01-22 12:51:22'),
(161, 13, 61, 66, 1, 0, 930.00, 1, NULL, 9, '2025-01-22 12:51:22', '2025-01-22 12:25:47', '2025-01-22 12:51:22'),
(162, 13, 62, 66, 1, 0, 273.00, 1, NULL, 9, '2025-01-22 12:51:29', '2025-01-22 12:27:13', '2025-01-22 12:51:29'),
(163, 10, 63, 59, 1, 0, 30.00, 9, 9, NULL, NULL, '2025-01-23 11:07:56', '2025-01-23 12:06:18'),
(164, 11, 63, 60, 1, 0, 50.00, 9, 9, NULL, NULL, '2025-01-23 11:07:56', '2025-01-23 12:41:15'),
(165, 12, 63, 61, 1, 0, 30.00, 9, 9, NULL, NULL, '2025-01-23 11:07:56', '2025-01-23 12:06:18'),
(166, 13, 63, 62, 1, 0, 30.00, 9, 9, 9, '2025-02-03 14:06:10', '2025-01-23 11:07:56', '2025-02-03 14:06:10'),
(167, 10, 64, 59, 1, 0, NULL, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(168, 11, 64, 60, 1, 0, NULL, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(169, 12, 64, 61, 1, 0, NULL, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(170, 13, 64, 62, 1, 0, NULL, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(171, 10, 65, 59, 1, 0, NULL, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(172, 11, 65, 60, 1, 0, NULL, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(173, 12, 65, 61, 1, 0, NULL, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(174, 13, 65, 62, 1, 0, NULL, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(175, 10, 66, 59, 1, 0, NULL, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:06', '2025-01-23 11:17:40'),
(176, 11, 66, 60, 1, 0, NULL, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:06', '2025-01-23 11:17:40'),
(177, 12, 66, 61, 1, 0, NULL, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:06', '2025-01-23 11:17:40'),
(178, 13, 66, 62, 1, 0, NULL, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(179, 10, 67, 59, 1, 0, NULL, 9, 9, NULL, NULL, '2025-01-23 11:23:28', '2025-01-23 12:23:59'),
(180, 11, 67, 60, 1, 0, NULL, 9, 9, NULL, NULL, '2025-01-23 11:23:28', '2025-01-23 12:23:59'),
(181, 12, 67, 61, 1, 0, NULL, 9, 9, NULL, NULL, '2025-01-23 11:23:28', '2025-01-23 12:23:59'),
(182, 13, 67, 62, 1, 0, NULL, 9, 9, 9, '2025-02-03 14:06:10', '2025-01-23 11:23:28', '2025-02-03 14:06:10'),
(183, 10, 51, 59, 1, 0, NULL, 9, NULL, NULL, NULL, '2025-01-26 09:19:00', '2025-01-26 09:19:00'),
(184, 10, 52, 59, 1, 0, 12.00, 9, NULL, NULL, NULL, '2025-01-26 09:19:00', '2025-01-26 09:19:00'),
(185, 10, 53, 59, 1, 0, 5.00, 9, NULL, NULL, NULL, '2025-01-26 09:19:00', '2025-01-26 09:19:00'),
(186, 10, 60, 59, 1, 0, 50.00, 9, NULL, 9, '2025-01-26 09:19:33', '2025-01-26 09:19:01', '2025-01-26 09:19:33'),
(187, 10, 69, 59, 1, 0, 20.00, 9, 9, 9, '2025-01-26 12:45:47', '2025-01-26 11:26:37', '2025-01-26 12:45:47'),
(188, 11, 69, 60, 1, 0, 20.00, 9, 9, 9, '2025-01-26 12:45:47', '2025-01-26 11:26:37', '2025-01-26 12:45:47'),
(189, 12, 69, 61, 1, 0, 20.00, 9, 9, 9, '2025-01-26 12:45:47', '2025-01-26 11:26:37', '2025-01-26 12:45:47'),
(190, 13, 69, 62, 1, 0, 20.00, 9, 9, 9, '2025-01-26 12:45:47', '2025-01-26 11:26:37', '2025-01-26 12:45:47'),
(191, 10, 82, 59, 1, 0, NULL, 9, NULL, NULL, NULL, '2025-02-02 09:08:14', '2025-02-02 09:08:14'),
(192, 11, 82, 60, 1, 0, NULL, 9, NULL, NULL, NULL, '2025-02-02 09:08:14', '2025-02-02 09:08:14'),
(193, 10, 83, 59, 1, 0, NULL, 9, NULL, NULL, NULL, '2025-02-03 09:21:57', '2025-02-03 09:21:57'),
(194, 11, 83, 60, 1, 0, NULL, 9, NULL, NULL, NULL, '2025-02-03 09:21:57', '2025-02-03 09:21:57'),
(195, 10, 84, 59, 1, 0, NULL, 9, NULL, NULL, NULL, '2025-02-03 09:25:03', '2025-02-03 09:25:03'),
(196, 11, 84, 60, 1, 0, NULL, 9, NULL, NULL, NULL, '2025-02-03 09:25:03', '2025-02-03 09:25:03'),
(197, 12, 84, 61, 1, 0, NULL, 9, NULL, NULL, NULL, '2025-02-03 09:25:03', '2025-02-03 09:25:03'),
(198, 10, 85, 59, 1, 0, NULL, 9, NULL, NULL, NULL, '2025-02-03 09:35:19', '2025-02-03 09:35:19'),
(199, 10, 86, 59, 1, 0, 120.00, 9, 9, 9, '2025-02-04 10:57:22', '2025-02-03 09:35:47', '2025-02-04 10:57:22');

-- --------------------------------------------------------

--
-- Table structure for table `branch_menu_addons`
--

CREATE TABLE `branch_menu_addons` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `dish_addon_id` bigint UNSIGNED DEFAULT NULL,
  `dish_id` bigint UNSIGNED DEFAULT NULL,
  `branch_menu_addon_category_id` bigint UNSIGNED DEFAULT NULL,
  `price` decimal(8,2) DEFAULT '0.00',
  `is_active` int NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_menu_addons`
--

INSERT INTO `branch_menu_addons` (`id`, `branch_id`, `dish_addon_id`, `dish_id`, `branch_menu_addon_category_id`, `price`, `is_active`, `created_by`, `modified_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(88, 10, 35, 54, 27, 11.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:22:20', '2025-01-21 10:35:25'),
(89, 11, 35, 54, 28, 11.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:22:20', '2025-01-19 10:11:21'),
(90, 12, 35, 54, 29, 11.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:22:20', '2025-01-19 10:23:24'),
(91, 13, 35, 54, 30, 11.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 12:22:21', '2025-02-03 14:06:10'),
(92, 10, 36, 56, 27, 20.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(93, 11, 36, 56, 28, 20.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(94, 12, 36, 56, 29, 20.00, 1, 9, 9, NULL, NULL, '2025-01-16 12:32:18', '2025-01-19 07:55:52'),
(95, 13, 36, 56, 30, 20.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 12:32:18', '2025-02-03 14:06:10'),
(96, 10, 37, 56, 27, 15.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(97, 11, 37, 56, 28, 15.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(98, 12, 37, 56, 29, 15.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(99, 13, 37, 56, 30, 15.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 12:32:18', '2025-02-03 14:06:10'),
(100, 10, 38, 56, 27, 20.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(101, 11, 38, 56, 28, 20.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(102, 12, 38, 56, 29, 20.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(103, 13, 38, 56, 30, 20.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 12:32:18', '2025-02-03 14:06:10'),
(104, 10, 39, 56, 27, 5.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(105, 11, 39, 56, 28, 5.00, 1, 9, 9, NULL, NULL, '2025-01-16 12:32:18', '2025-01-22 12:57:58'),
(106, 12, 39, 56, 29, 5.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(107, 13, 39, 56, 30, 5.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 12:32:18', '2025-02-03 14:06:10'),
(108, 10, 40, 57, 27, 5.00, 1, 9, NULL, NULL, '2025-01-22 07:38:19', '2025-01-16 14:29:25', '2025-01-22 07:38:19'),
(109, 11, 40, 57, 28, 5.00, 1, 9, NULL, NULL, '2025-01-22 07:38:19', '2025-01-16 14:29:25', '2025-01-22 07:38:19'),
(110, 12, 40, 57, 29, 5.00, 1, 9, NULL, NULL, '2025-01-22 07:38:19', '2025-01-16 14:29:25', '2025-01-22 07:38:19'),
(111, 13, 40, 57, 30, 5.00, 1, 9, NULL, NULL, '2025-01-22 07:38:19', '2025-01-16 14:29:25', '2025-01-22 07:38:19'),
(112, 10, 41, 57, 27, 5.00, 1, 9, NULL, NULL, NULL, '2025-01-16 14:29:25', '2025-01-16 14:29:25'),
(113, 11, 41, 57, 28, 5.00, 1, 9, NULL, NULL, NULL, '2025-01-16 14:29:25', '2025-01-16 14:29:25'),
(114, 12, 41, 57, 29, 5.00, 1, 9, NULL, NULL, NULL, '2025-01-16 14:29:25', '2025-01-16 14:29:25'),
(115, 13, 41, 57, 30, 5.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 14:29:25', '2025-02-03 14:06:10'),
(116, 10, 42, 57, 27, 5.00, 1, 9, NULL, NULL, '2025-01-22 07:38:19', '2025-01-16 14:29:25', '2025-01-22 07:38:19'),
(117, 11, 42, 57, 28, 5.00, 1, 9, NULL, NULL, '2025-01-22 07:38:19', '2025-01-16 14:29:25', '2025-01-22 07:38:19'),
(118, 12, 42, 57, 29, 5.00, 1, 9, NULL, NULL, '2025-01-22 07:38:19', '2025-01-16 14:29:25', '2025-01-22 07:38:19'),
(119, 13, 42, 57, 30, 5.00, 1, 9, NULL, NULL, '2025-01-22 07:38:19', '2025-01-16 14:29:25', '2025-01-22 07:38:19'),
(120, 10, 43, 57, 27, 5.00, 1, 9, NULL, NULL, '2025-01-22 07:38:19', '2025-01-16 14:29:25', '2025-01-22 07:38:19'),
(121, 11, 43, 57, 28, 5.00, 1, 9, NULL, NULL, '2025-01-22 07:38:19', '2025-01-16 14:29:25', '2025-01-22 07:38:19'),
(122, 12, 43, 57, 29, 5.00, 1, 9, NULL, NULL, '2025-01-22 07:38:19', '2025-01-16 14:29:25', '2025-01-22 07:38:19'),
(123, 13, 43, 57, 30, 5.00, 1, 9, NULL, NULL, '2025-01-22 07:38:19', '2025-01-16 14:29:25', '2025-01-22 07:38:19'),
(124, 10, 44, 58, 27, 30.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:52', '2025-01-19 10:51:32'),
(125, 11, 44, 58, 28, 30.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:52', '2025-01-19 10:51:32'),
(126, 12, 44, 58, 29, 30.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:52', '2025-01-19 10:51:32'),
(127, 13, 44, 58, 30, 30.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:52', '2025-01-19 10:51:32'),
(128, 10, 45, 58, 27, 5.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:52', '2025-01-19 10:51:32'),
(129, 11, 45, 58, 28, 5.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:52', '2025-01-19 10:51:32'),
(130, 12, 45, 58, 29, 5.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:52', '2025-01-19 10:51:32'),
(131, 13, 45, 58, 30, 5.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:52', '2025-01-19 10:51:32'),
(132, 10, 46, 58, 27, 10.00, 1, 9, NULL, NULL, '2025-01-19 09:56:33', '2025-01-19 09:48:36', '2025-01-19 09:56:33'),
(133, 11, 46, 58, 28, 10.00, 1, 9, NULL, NULL, '2025-01-19 09:56:33', '2025-01-19 09:48:36', '2025-01-19 09:56:33'),
(134, 12, 46, 58, 29, 10.00, 1, 9, NULL, NULL, '2025-01-19 09:56:33', '2025-01-19 09:48:36', '2025-01-19 09:56:33'),
(135, 13, 46, 58, 30, 10.00, 1, 9, NULL, NULL, '2025-01-19 09:56:33', '2025-01-19 09:48:36', '2025-01-19 09:56:33'),
(136, 10, 47, 58, 27, 15.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:48:36', '2025-01-19 10:51:32'),
(137, 11, 47, 58, 28, 15.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:48:36', '2025-01-19 10:51:32'),
(138, 12, 47, 58, 29, 15.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:48:36', '2025-01-19 10:51:32'),
(139, 13, 47, 58, 30, 15.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:48:36', '2025-01-19 10:51:32'),
(140, 10, 48, 58, 31, 20.00, 1, 9, NULL, NULL, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(141, 11, 48, 58, 32, 20.00, 1, 9, NULL, NULL, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(142, 12, 48, 58, 33, 20.00, 1, 9, NULL, NULL, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(143, 13, 48, 58, 34, 20.00, 1, 9, NULL, NULL, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(144, 10, 49, 58, 31, 5.00, 1, 9, NULL, NULL, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(145, 11, 49, 58, 32, 5.00, 1, 9, NULL, NULL, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(146, 12, 49, 58, 33, 5.00, 1, 9, NULL, NULL, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(147, 13, 49, 58, 34, 5.00, 1, 9, NULL, NULL, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(148, 10, 50, 58, 31, 15.00, 1, 9, NULL, NULL, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(149, 11, 50, 58, 32, 15.00, 1, 9, NULL, NULL, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(150, 12, 50, 58, 33, 15.00, 1, 9, NULL, NULL, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(151, 13, 50, 58, 34, 15.00, 1, 9, NULL, NULL, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(152, 10, 51, 59, 27, 10.00, 1, 9, NULL, 9, '2025-01-19 10:31:08', '2025-01-19 10:09:48', '2025-01-19 10:31:08'),
(153, 10, 52, 59, 27, 5.00, 1, 9, NULL, 9, '2025-01-19 10:31:08', '2025-01-19 10:09:48', '2025-01-19 10:31:08'),
(154, 11, 34, 52, 28, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-19 10:11:21', '2025-01-22 11:08:29'),
(155, 11, 51, 59, 28, 10.00, 1, 9, NULL, 9, '2025-01-19 10:31:08', '2025-01-19 10:11:21', '2025-01-19 10:31:08'),
(156, 11, 52, 59, 28, 5.00, 1, 9, NULL, 9, '2025-01-19 10:31:08', '2025-01-19 10:11:21', '2025-01-19 10:31:08'),
(157, 12, 34, 52, 29, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-19 10:23:24', '2025-01-22 11:08:29'),
(158, 12, 51, 59, 29, 10.00, 1, 9, NULL, 9, '2025-01-19 10:31:08', '2025-01-19 10:23:24', '2025-01-19 10:31:08'),
(159, 12, 52, 59, 29, 5.00, 1, 9, NULL, 9, '2025-01-19 10:31:08', '2025-01-19 10:23:25', '2025-01-19 10:31:08'),
(160, 10, 53, 60, 27, 10.00, 1, 9, NULL, NULL, '2025-01-20 10:10:04', '2025-01-20 10:06:26', '2025-01-20 10:10:04'),
(161, 11, 53, 60, 28, 10.00, 1, 9, NULL, NULL, '2025-01-20 10:10:04', '2025-01-20 10:06:26', '2025-01-20 10:10:04'),
(162, 12, 53, 60, 29, 10.00, 1, 9, NULL, NULL, '2025-01-20 10:10:04', '2025-01-20 10:06:26', '2025-01-20 10:10:04'),
(163, 13, 53, 60, 30, 10.00, 1, 9, NULL, NULL, '2025-01-20 10:10:04', '2025-01-20 10:06:26', '2025-01-20 10:10:04'),
(164, 10, 54, 60, 27, 10.00, 1, 9, NULL, NULL, '2025-01-20 10:10:04', '2025-01-20 10:06:26', '2025-01-20 10:10:04'),
(165, 11, 54, 60, 28, 10.00, 1, 9, NULL, NULL, '2025-01-20 10:10:04', '2025-01-20 10:06:26', '2025-01-20 10:10:04'),
(166, 12, 54, 60, 29, 10.00, 1, 9, NULL, NULL, '2025-01-20 10:10:04', '2025-01-20 10:06:26', '2025-01-20 10:10:04'),
(167, 13, 54, 60, 30, 10.00, 1, 9, NULL, NULL, '2025-01-20 10:10:04', '2025-01-20 10:06:26', '2025-01-20 10:10:04'),
(168, 10, 55, 60, 27, 10.00, 1, 9, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:06:26', '2025-01-21 10:14:25'),
(169, 11, 55, 60, 28, 10.00, 1, 9, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:06:26', '2025-01-21 10:14:25'),
(170, 12, 55, 60, 29, 10.00, 1, 9, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:06:26', '2025-01-21 10:14:25'),
(171, 13, 55, 60, 30, 10.00, 1, 67, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:06:26', '2025-01-21 10:14:25'),
(172, 10, 56, 60, 27, 10.00, 1, 9, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:09:31', '2025-01-21 10:14:25'),
(173, 11, 56, 60, 28, 10.00, 1, 9, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:09:31', '2025-01-21 10:14:25'),
(174, 12, 56, 60, 29, 10.00, 1, 9, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:09:31', '2025-01-21 10:14:25'),
(175, 13, 56, 60, 30, 10.00, 1, 67, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:09:31', '2025-01-21 10:14:25'),
(176, 10, 57, 60, 27, 10.00, 1, 9, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:09:31', '2025-01-21 10:14:25'),
(177, 11, 57, 60, 28, 10.00, 1, 9, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:09:31', '2025-01-21 10:14:25'),
(178, 12, 57, 60, 29, 10.00, 1, 9, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:09:31', '2025-01-21 10:14:25'),
(179, 13, 57, 60, 30, 10.00, 1, 67, NULL, 9, '2025-01-21 10:14:25', '2025-01-20 10:09:31', '2025-01-21 10:14:25'),
(180, 13, 34, 52, 30, 10.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:46', '2025-02-03 14:06:10'),
(181, 13, 44, 58, 30, 30.00, 1, 67, NULL, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:46', '2025-02-03 14:06:10'),
(182, 13, 45, 58, 30, 5.00, 1, 67, NULL, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:46', '2025-02-03 14:06:10'),
(183, 13, 47, 58, 30, 15.00, 1, 67, NULL, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:46', '2025-02-03 14:06:10'),
(184, 13, 51, 59, 30, 10.00, 1, 67, NULL, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:46', '2025-02-03 14:06:10'),
(185, 13, 52, 59, 30, 5.00, 1, 67, NULL, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:46', '2025-02-03 14:06:10'),
(186, 11, 58, 52, 28, 15.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:08:29', '2025-01-22 11:08:29'),
(187, 12, 58, 52, 29, 15.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:08:29', '2025-01-22 11:08:29'),
(188, 13, 58, 52, 30, 15.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-22 11:08:29', '2025-02-03 14:06:10'),
(189, 11, 59, 52, 28, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:08:29', '2025-01-22 11:08:29'),
(190, 12, 59, 52, 29, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:08:29', '2025-01-22 11:08:29'),
(191, 13, 59, 52, 30, 10.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-22 11:08:30', '2025-02-03 14:06:10'),
(192, 11, 60, 52, 28, 15.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:08:30', '2025-01-22 11:08:30'),
(193, 12, 60, 52, 29, 15.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:08:30', '2025-01-22 11:08:30'),
(194, 13, 60, 52, 30, 15.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-22 11:08:30', '2025-02-03 14:06:10'),
(195, 10, 61, 54, 27, 12.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:09:05', '2025-01-22 11:09:05'),
(196, 11, 61, 54, 28, 12.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:09:05', '2025-01-22 11:09:05'),
(197, 12, 61, 54, 29, 12.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:09:05', '2025-01-22 11:09:05'),
(198, 13, 61, 54, 30, 12.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-22 11:09:06', '2025-02-03 14:06:10'),
(199, 10, 62, 54, 27, 13.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:09:06', '2025-01-22 11:09:06'),
(200, 11, 62, 54, 28, 13.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:09:06', '2025-01-22 11:09:06'),
(201, 12, 62, 54, 29, 13.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:09:06', '2025-01-22 11:09:06'),
(202, 13, 62, 54, 30, 13.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-22 11:09:06', '2025-02-03 14:06:10'),
(203, 13, 63, 62, 30, 12.97, 1, 1, NULL, 9, '2025-01-22 12:51:29', '2025-01-22 12:27:13', '2025-01-22 12:51:29'),
(204, 10, 64, 64, 27, 10.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(205, 11, 64, 64, 28, 10.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(206, 12, 64, 64, 29, 10.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(207, 13, 64, 64, 30, 10.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(208, 10, 65, 64, 27, 20.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(209, 11, 65, 64, 28, 20.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(210, 12, 65, 64, 29, 20.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(211, 13, 65, 64, 30, 20.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(212, 10, 66, 65, 27, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(213, 11, 66, 65, 28, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(214, 12, 66, 65, 29, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(215, 13, 66, 65, 30, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(216, 10, 67, 65, 27, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(217, 11, 67, 65, 28, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(218, 12, 67, 65, 29, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(219, 13, 67, 65, 30, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(220, 10, 68, 65, 27, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(221, 11, 68, 65, 28, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(222, 12, 68, 65, 29, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:56', '2025-01-23 11:17:47'),
(223, 13, 68, 65, 30, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:57', '2025-01-23 11:17:47'),
(224, 10, 69, 66, 27, 10.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(225, 11, 69, 66, 28, 10.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(226, 12, 69, 66, 29, 10.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(227, 13, 69, 66, 30, 10.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(228, 10, 70, 66, 27, 20.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(229, 11, 70, 66, 28, 20.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(230, 12, 70, 66, 29, 20.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(231, 13, 70, 66, 30, 20.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(232, 10, 71, 67, 27, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:13:53', '2025-01-23 11:23:28', '2025-01-23 13:13:53'),
(233, 11, 71, 67, 28, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:13:53', '2025-01-23 11:23:28', '2025-01-23 13:13:53'),
(234, 12, 71, 67, 29, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:13:53', '2025-01-23 11:23:28', '2025-01-23 13:13:53'),
(235, 13, 71, 67, 30, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:13:53', '2025-01-23 11:23:28', '2025-01-23 13:13:53'),
(236, 10, 72, 67, 27, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:13:53', '2025-01-23 11:23:28', '2025-01-23 13:13:53'),
(237, 11, 72, 67, 28, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:13:53', '2025-01-23 11:23:28', '2025-01-23 13:13:53'),
(238, 12, 72, 67, 29, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:13:53', '2025-01-23 11:23:28', '2025-01-23 13:13:53'),
(239, 13, 72, 67, 30, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:13:53', '2025-01-23 11:23:28', '2025-01-23 13:13:53'),
(240, 10, 73, 63, 27, 5.00, 1, 9, NULL, NULL, NULL, '2025-01-23 12:06:18', '2025-01-23 12:06:18'),
(241, 11, 73, 63, 28, 5.00, 1, 9, NULL, NULL, NULL, '2025-01-23 12:06:18', '2025-01-23 12:06:18'),
(242, 12, 73, 63, 29, 5.00, 1, 9, NULL, NULL, NULL, '2025-01-23 12:06:18', '2025-01-23 12:06:18'),
(243, 13, 73, 63, 30, 5.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-23 12:06:18', '2025-02-03 14:06:10'),
(244, 10, 74, 63, 27, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-23 12:06:18', '2025-01-23 12:06:18'),
(245, 11, 74, 63, 28, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-23 12:06:18', '2025-01-23 12:06:18'),
(246, 12, 74, 63, 29, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-23 12:06:18', '2025-01-23 12:06:18'),
(247, 13, 74, 63, 30, 10.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-23 12:06:18', '2025-02-03 14:06:10'),
(248, 10, 75, 67, 39, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:24:20', '2025-01-23 13:13:29', '2025-01-23 13:24:20'),
(249, 11, 75, 67, 40, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:24:20', '2025-01-23 13:13:29', '2025-01-23 13:24:20'),
(250, 12, 75, 67, 41, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:24:20', '2025-01-23 13:13:29', '2025-01-23 13:24:20'),
(251, 13, 75, 67, 42, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:24:20', '2025-01-23 13:13:29', '2025-01-23 13:24:20'),
(252, 10, 76, 67, 27, 15.00, 1, 9, NULL, NULL, NULL, '2025-01-23 13:23:32', '2025-01-23 13:23:32'),
(253, 11, 76, 67, 28, 15.00, 1, 9, NULL, NULL, NULL, '2025-01-23 13:23:32', '2025-01-23 13:23:32'),
(254, 12, 76, 67, 29, 15.00, 1, 9, NULL, NULL, NULL, '2025-01-23 13:23:32', '2025-01-23 13:23:32'),
(255, 13, 76, 67, 30, 15.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-23 13:23:32', '2025-02-03 14:06:10'),
(256, 10, 77, 67, 27, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-23 13:23:32', '2025-01-23 13:23:32'),
(257, 11, 77, 67, 28, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-23 13:23:32', '2025-01-23 13:23:32'),
(258, 12, 77, 67, 29, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-23 13:23:32', '2025-01-23 13:23:32'),
(259, 13, 77, 67, 30, 10.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-23 13:23:32', '2025-02-03 14:06:10'),
(260, 10, 78, 67, 39, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:27:01', '2025-01-23 13:25:51', '2025-01-23 13:27:01'),
(261, 11, 78, 67, 40, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:27:01', '2025-01-23 13:25:51', '2025-01-23 13:27:01'),
(262, 12, 78, 67, 41, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:27:01', '2025-01-23 13:25:51', '2025-01-23 13:27:01'),
(263, 13, 78, 67, 42, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:27:01', '2025-01-23 13:25:51', '2025-01-23 13:27:01'),
(264, 10, 79, 67, 39, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:27:01', '2025-01-23 13:25:51', '2025-01-23 13:27:01'),
(265, 11, 79, 67, 40, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:27:01', '2025-01-23 13:25:51', '2025-01-23 13:27:01'),
(266, 12, 79, 67, 41, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:27:01', '2025-01-23 13:25:51', '2025-01-23 13:27:01'),
(267, 13, 79, 67, 42, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:27:01', '2025-01-23 13:25:51', '2025-01-23 13:27:01'),
(268, 10, 80, 67, 39, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:27:42', '2025-01-23 13:27:01', '2025-01-23 13:27:42'),
(269, 11, 80, 67, 40, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:27:42', '2025-01-23 13:27:01', '2025-01-23 13:27:42'),
(270, 12, 80, 67, 41, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:27:42', '2025-01-23 13:27:01', '2025-01-23 13:27:42'),
(271, 13, 80, 67, 42, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:27:42', '2025-01-23 13:27:01', '2025-01-23 13:27:42'),
(272, 10, 81, 67, 39, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:27:42', '2025-01-23 13:27:01', '2025-01-23 13:27:42'),
(273, 11, 81, 67, 40, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:27:42', '2025-01-23 13:27:01', '2025-01-23 13:27:42'),
(274, 12, 81, 67, 41, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:27:42', '2025-01-23 13:27:01', '2025-01-23 13:27:42'),
(275, 13, 81, 67, 42, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:27:42', '2025-01-23 13:27:01', '2025-01-23 13:27:42'),
(276, 10, 82, 67, 39, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:35:44', '2025-01-23 13:34:16', '2025-01-23 13:35:44'),
(277, 11, 82, 67, 40, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:35:44', '2025-01-23 13:34:16', '2025-01-23 13:35:44'),
(278, 12, 82, 67, 41, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:35:44', '2025-01-23 13:34:16', '2025-01-23 13:35:44'),
(279, 13, 82, 67, 42, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:35:44', '2025-01-23 13:34:16', '2025-01-23 13:35:44'),
(280, 10, 83, 67, 39, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:35:44', '2025-01-23 13:34:16', '2025-01-23 13:35:44'),
(281, 11, 83, 67, 40, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:35:44', '2025-01-23 13:34:16', '2025-01-23 13:35:44'),
(282, 12, 83, 67, 41, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:35:44', '2025-01-23 13:34:16', '2025-01-23 13:35:44'),
(283, 13, 83, 67, 42, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:35:44', '2025-01-23 13:34:16', '2025-01-23 13:35:44'),
(284, 10, 84, 67, 39, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:38:00', '2025-01-23 13:36:01', '2025-01-23 13:38:00'),
(285, 11, 84, 67, 40, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:38:00', '2025-01-23 13:36:01', '2025-01-23 13:38:00'),
(286, 12, 84, 67, 41, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:38:00', '2025-01-23 13:36:01', '2025-01-23 13:38:00'),
(287, 13, 84, 67, 42, 15.00, 1, 9, NULL, NULL, '2025-01-23 13:38:00', '2025-01-23 13:36:01', '2025-01-23 13:38:00'),
(288, 10, 85, 67, 39, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:38:00', '2025-01-23 13:36:01', '2025-01-23 13:38:00'),
(289, 11, 85, 67, 40, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:38:00', '2025-01-23 13:36:01', '2025-01-23 13:38:00'),
(290, 12, 85, 67, 41, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:38:00', '2025-01-23 13:36:01', '2025-01-23 13:38:00'),
(291, 13, 85, 67, 42, 10.00, 1, 9, NULL, NULL, '2025-01-23 13:38:00', '2025-01-23 13:36:01', '2025-01-23 13:38:00'),
(292, 10, 86, 67, 27, 12.00, 1, 9, NULL, NULL, '2025-01-23 14:04:11', '2025-01-23 14:03:26', '2025-01-23 14:04:11'),
(293, 11, 86, 67, 28, 12.00, 1, 9, NULL, NULL, '2025-01-23 14:04:11', '2025-01-23 14:03:26', '2025-01-23 14:04:11'),
(294, 12, 86, 67, 29, 12.00, 1, 9, NULL, NULL, '2025-01-23 14:04:11', '2025-01-23 14:03:26', '2025-01-23 14:04:11'),
(295, 13, 86, 67, 30, 12.00, 1, 9, NULL, NULL, '2025-01-23 14:04:11', '2025-01-23 14:03:26', '2025-01-23 14:04:11'),
(296, 10, 87, 67, 27, 20.00, 1, 9, NULL, NULL, '2025-01-23 14:06:00', '2025-01-23 14:05:38', '2025-01-23 14:06:00'),
(297, 11, 87, 67, 28, 20.00, 1, 9, NULL, NULL, '2025-01-23 14:06:00', '2025-01-23 14:05:38', '2025-01-23 14:06:00'),
(298, 12, 87, 67, 29, 20.00, 1, 9, NULL, NULL, '2025-01-23 14:06:00', '2025-01-23 14:05:38', '2025-01-23 14:06:00'),
(299, 13, 87, 67, 30, 20.00, 1, 9, NULL, NULL, '2025-01-23 14:06:00', '2025-01-23 14:05:38', '2025-01-23 14:06:00'),
(300, 10, 34, 52, 27, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:01', '2025-01-26 09:19:01'),
(301, 10, 44, 58, 27, 30.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:01', '2025-01-26 09:19:01'),
(302, 10, 45, 58, 27, 5.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:01', '2025-01-26 09:19:01'),
(303, 10, 47, 58, 27, 15.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:01', '2025-01-26 09:19:01'),
(304, 10, 51, 59, 27, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:01', '2025-01-26 09:19:01'),
(305, 10, 52, 59, 27, 5.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:01', '2025-01-26 09:19:01'),
(306, 10, 55, 60, 27, 10.00, 1, 9, NULL, 9, '2025-01-26 09:19:33', '2025-01-26 09:19:01', '2025-01-26 09:19:33'),
(307, 10, 56, 60, 27, 10.00, 1, 9, NULL, 9, '2025-01-26 09:19:33', '2025-01-26 09:19:01', '2025-01-26 09:19:33'),
(308, 10, 57, 60, 27, 10.00, 1, 9, NULL, 9, '2025-01-26 09:19:33', '2025-01-26 09:19:01', '2025-01-26 09:19:33'),
(309, 10, 58, 52, 27, 15.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:01', '2025-01-26 09:19:01'),
(310, 10, 59, 52, 27, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:01', '2025-01-26 09:19:01'),
(311, 10, 60, 52, 27, 15.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:02', '2025-01-26 09:19:02'),
(312, 10, 63, 62, 27, 12.97, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:02', '2025-01-26 09:19:02'),
(313, 10, 64, 64, 27, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:02', '2025-01-26 09:19:02'),
(314, 10, 65, 64, 27, 20.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:02', '2025-01-26 09:19:02'),
(315, 10, 66, 65, 27, 5.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:02', '2025-01-26 09:19:02'),
(316, 10, 67, 65, 27, 5.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:02', '2025-01-26 09:19:02'),
(317, 10, 68, 65, 27, 5.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:02', '2025-01-26 09:19:02'),
(318, 10, 69, 66, 27, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:02', '2025-01-26 09:19:02'),
(319, 10, 70, 66, 27, 20.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:02', '2025-01-26 09:19:02'),
(320, 10, 88, 82, 27, 11.00, 1, 9, NULL, NULL, NULL, '2025-02-02 09:08:14', '2025-02-02 09:08:14'),
(321, 11, 88, 82, 28, 11.00, 1, 9, NULL, NULL, NULL, '2025-02-02 09:08:14', '2025-02-02 09:08:14');

-- --------------------------------------------------------

--
-- Table structure for table `branch_menu_addon_categories`
--

CREATE TABLE `branch_menu_addon_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `addon_category_id` bigint UNSIGNED DEFAULT NULL,
  `is_active` int DEFAULT '0',
  `created_by` bigint UNSIGNED NOT NULL DEFAULT '10',
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_menu_addon_categories`
--

INSERT INTO `branch_menu_addon_categories` (`id`, `branch_id`, `addon_category_id`, `is_active`, `created_by`, `modified_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(27, 10, 1, 1, 9, NULL, NULL, NULL, '2025-01-16 12:22:20', '2025-01-16 12:32:18'),
(28, 11, 1, 1, 9, NULL, NULL, NULL, '2025-01-16 12:22:20', '2025-01-16 12:32:18'),
(29, 12, 1, 1, 9, NULL, NULL, NULL, '2025-01-16 12:22:20', '2025-01-16 12:32:18'),
(30, 13, 1, 1, 9, NULL, NULL, NULL, '2025-01-16 12:22:20', '2025-01-23 11:14:28'),
(31, 10, 1, 1, 9, NULL, NULL, NULL, '2025-01-19 09:59:38', '2025-01-19 09:59:38'),
(32, 11, 1, 1, 9, NULL, NULL, NULL, '2025-01-19 09:59:38', '2025-01-19 09:59:38'),
(33, 12, 1, 1, 9, NULL, NULL, NULL, '2025-01-19 09:59:38', '2025-01-19 09:59:38'),
(34, 13, 1, 1, 9, NULL, NULL, NULL, '2025-01-19 09:59:38', '2025-01-19 09:59:38'),
(35, 13, 1, 1, 67, NULL, NULL, NULL, '2025-01-20 15:28:46', '2025-01-20 15:28:46'),
(36, 13, 1, 1, 67, NULL, NULL, NULL, '2025-01-20 15:28:46', '2025-01-20 15:28:46'),
(37, 13, 1, 1, 67, NULL, NULL, NULL, '2025-01-20 15:28:46', '2025-01-20 15:28:46'),
(38, 13, 1, 1, 67, NULL, NULL, NULL, '2025-01-20 15:28:46', '2025-01-20 15:28:46'),
(39, 10, 1, 1, 9, NULL, NULL, NULL, '2025-01-23 13:13:28', '2025-01-23 13:13:28'),
(40, 11, 1, 1, 9, NULL, NULL, NULL, '2025-01-23 13:13:28', '2025-01-23 13:13:28'),
(41, 12, 1, 1, 9, NULL, NULL, NULL, '2025-01-23 13:13:28', '2025-01-23 13:13:28'),
(42, 13, 1, 1, 9, NULL, NULL, NULL, '2025-01-23 13:13:28', '2025-01-23 13:13:28'),
(43, 10, 1, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:01', '2025-01-26 09:19:01');

-- --------------------------------------------------------

--
-- Table structure for table `branch_menu_categories`
--

CREATE TABLE `branch_menu_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `dish_category_id` bigint UNSIGNED DEFAULT NULL COMMENT 'refer to dish category table',
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_menu_categories`
--

INSERT INTO `branch_menu_categories` (`id`, `dish_category_id`, `branch_id`, `is_active`, `created_by`, `modified_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(59, 16, 10, 1, 9, 9, NULL, NULL, '2025-01-16 12:00:04', '2025-01-22 10:21:03'),
(60, 16, 11, 1, 9, 9, NULL, NULL, '2025-01-16 12:00:04', '2025-01-23 08:10:28'),
(61, 16, 12, 1, 9, NULL, NULL, NULL, '2025-01-16 12:00:04', '2025-01-16 12:30:21'),
(62, 16, 13, 1, 9, 9, NULL, NULL, '2025-01-16 12:00:04', '2025-01-21 10:35:25'),
(63, 17, 10, 1, 9, NULL, NULL, NULL, '2025-01-16 14:29:25', '2025-01-23 12:00:37'),
(64, 17, 11, 1, 9, 9, NULL, NULL, '2025-01-16 14:29:25', '2025-01-23 12:00:37'),
(65, 17, 12, 1, 9, NULL, NULL, NULL, '2025-01-16 14:29:25', '2025-01-23 12:00:37'),
(66, 17, 13, 1, 9, NULL, NULL, NULL, '2025-01-16 14:29:25', '2025-01-23 12:00:37');

-- --------------------------------------------------------

--
-- Table structure for table `branch_menu_sizes`
--

CREATE TABLE `branch_menu_sizes` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `dish_size_id` bigint UNSIGNED DEFAULT NULL,
  `dish_id` bigint UNSIGNED DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_menu_sizes`
--

INSERT INTO `branch_menu_sizes` (`id`, `branch_id`, `dish_size_id`, `dish_id`, `price`, `is_active`, `created_by`, `modified_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(88, 10, 36, 55, 70.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:30:21', '2025-01-16 12:30:21'),
(89, 11, 36, 55, 70.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:30:21', '2025-01-16 12:30:21'),
(90, 12, 36, 55, 70.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:30:22', '2025-01-16 12:30:22'),
(91, 13, 36, 55, 70.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 12:30:22', '2025-02-03 14:06:10'),
(92, 10, 37, 55, 60.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:30:22', '2025-01-16 12:30:22'),
(93, 11, 37, 55, 60.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:30:22', '2025-01-16 12:30:22'),
(94, 12, 37, 55, 60.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:30:22', '2025-01-16 12:30:22'),
(95, 13, 37, 55, 60.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 12:30:22', '2025-02-03 14:06:10'),
(96, 10, 38, 55, 50.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:30:22', '2025-01-16 12:30:22'),
(97, 11, 38, 55, 50.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:30:22', '2025-01-16 12:30:22'),
(98, 12, 38, 55, 50.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:30:22', '2025-01-16 12:30:22'),
(99, 13, 38, 55, 50.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 12:30:22', '2025-02-03 14:06:10'),
(100, 10, 39, 56, 70.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(101, 11, 39, 56, 70.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(102, 12, 39, 56, 70.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(103, 13, 39, 56, 70.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 12:32:18', '2025-02-03 14:06:10'),
(104, 10, 40, 56, 60.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(105, 11, 40, 56, 60.00, 1, 9, 9, NULL, NULL, '2025-01-16 12:32:18', '2025-01-22 12:57:34'),
(106, 12, 40, 56, 60.00, 1, 9, NULL, NULL, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(107, 13, 40, 56, 60.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 12:32:18', '2025-02-03 14:06:10'),
(108, 10, 41, 57, 99.00, 1, 9, NULL, NULL, NULL, '2025-01-16 14:29:25', '2025-01-16 14:29:25'),
(109, 11, 41, 57, 99.00, 1, 9, NULL, NULL, NULL, '2025-01-16 14:29:25', '2025-01-16 14:29:25'),
(110, 12, 41, 57, 99.00, 1, 9, NULL, NULL, NULL, '2025-01-16 14:29:25', '2025-01-16 14:29:25'),
(111, 13, 41, 57, 99.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-16 14:29:25', '2025-02-03 14:06:10'),
(112, 10, 42, 58, 60.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:53', '2025-01-19 10:51:32'),
(113, 11, 42, 58, 60.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:53', '2025-01-19 10:51:32'),
(114, 12, 42, 58, 60.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:53', '2025-01-19 10:51:32'),
(115, 13, 42, 58, 60.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:53', '2025-01-19 10:51:32'),
(116, 10, 43, 58, 40.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:53', '2025-01-19 10:51:32'),
(117, 11, 43, 58, 40.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:53', '2025-01-19 10:51:32'),
(118, 12, 43, 58, 40.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:53', '2025-01-19 10:51:32'),
(119, 13, 43, 58, 40.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:53', '2025-01-19 10:51:32'),
(120, 10, 44, 58, 30.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:53', '2025-01-19 10:51:32'),
(121, 11, 44, 58, 30.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:53', '2025-01-19 10:51:32'),
(122, 12, 44, 58, 30.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:53', '2025-01-19 10:51:32'),
(123, 13, 44, 58, 30.00, 1, 9, NULL, 9, '2025-01-19 10:51:32', '2025-01-19 09:03:53', '2025-01-19 10:51:32'),
(124, 10, 45, 58, 100.00, 1, 9, NULL, NULL, '2025-01-19 10:06:59', '2025-01-19 09:48:36', '2025-01-19 10:06:59'),
(125, 11, 45, 58, 100.00, 1, 9, NULL, NULL, '2025-01-19 10:06:59', '2025-01-19 09:48:36', '2025-01-19 10:06:59'),
(126, 12, 45, 58, 100.00, 1, 9, NULL, NULL, '2025-01-19 10:06:59', '2025-01-19 09:48:36', '2025-01-19 10:06:59'),
(127, 13, 45, 58, 100.00, 1, 9, NULL, NULL, '2025-01-19 10:06:59', '2025-01-19 09:48:36', '2025-01-19 10:06:59'),
(128, 10, 46, 59, 10.00, 1, 9, NULL, 9, '2025-01-19 10:31:08', '2025-01-19 10:09:48', '2025-01-19 10:31:08'),
(129, 11, 34, 51, 30.00, 1, 9, NULL, NULL, NULL, '2025-01-19 10:11:21', '2025-01-19 10:11:21'),
(130, 11, 35, 52, 12.00, 1, 9, NULL, NULL, NULL, '2025-01-19 10:11:22', '2025-01-19 10:11:22'),
(131, 11, 46, 59, 10.00, 1, 9, NULL, 9, '2025-01-19 10:31:08', '2025-01-19 10:11:22', '2025-01-19 10:31:08'),
(132, 12, 34, 51, 30.00, 1, 9, NULL, NULL, NULL, '2025-01-19 10:23:25', '2025-01-19 10:23:25'),
(133, 12, 35, 52, 12.00, 1, 9, NULL, NULL, NULL, '2025-01-19 10:23:25', '2025-01-19 10:23:25'),
(134, 12, 46, 59, 10.00, 1, 9, NULL, 9, '2025-01-19 10:31:08', '2025-01-19 10:23:25', '2025-01-19 10:31:08'),
(135, 13, 34, 51, 30.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:46', '2025-02-03 14:06:10'),
(136, 13, 35, 52, 12.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:46', '2025-02-03 14:06:10'),
(137, 13, 42, 58, 60.00, 1, 67, NULL, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:47', '2025-02-03 14:06:10'),
(138, 13, 43, 58, 40.00, 1, 67, NULL, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:47', '2025-02-03 14:06:10'),
(139, 13, 44, 58, 30.00, 1, 67, NULL, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:47', '2025-02-03 14:06:10'),
(140, 13, 46, 59, 10.00, 1, 67, NULL, 9, '2025-02-03 14:06:10', '2025-01-20 15:28:47', '2025-02-03 14:06:10'),
(141, 11, 47, 51, 25.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:06:22', '2025-01-22 11:06:22'),
(142, 12, 47, 51, 25.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:06:22', '2025-01-22 11:06:22'),
(143, 13, 47, 51, 25.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-22 11:06:22', '2025-02-03 14:06:10'),
(144, 11, 48, 52, 20.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:08:30', '2025-01-22 11:08:30'),
(145, 12, 48, 52, 20.00, 1, 9, NULL, NULL, NULL, '2025-01-22 11:08:30', '2025-01-22 11:08:30'),
(146, 13, 48, 52, 20.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-22 11:08:30', '2025-02-03 14:06:10'),
(147, 13, 49, 62, 34.00, 1, 1, NULL, 9, '2025-01-22 12:51:29', '2025-01-22 12:27:13', '2025-01-22 12:51:29'),
(148, 10, 50, 64, 10.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(149, 11, 50, 64, 10.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(150, 12, 50, 64, 10.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(151, 13, 50, 64, 10.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(152, 10, 51, 64, 80.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(153, 11, 51, 64, 80.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(154, 12, 51, 64, 80.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(155, 13, 51, 64, 80.00, 1, 9, NULL, 9, '2025-01-23 11:17:55', '2025-01-23 11:14:28', '2025-01-23 11:17:55'),
(156, 10, 52, 65, 100.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:57', '2025-01-23 11:17:47'),
(157, 11, 52, 65, 100.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:57', '2025-01-23 11:17:47'),
(158, 12, 52, 65, 100.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:57', '2025-01-23 11:17:47'),
(159, 13, 52, 65, 100.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:57', '2025-01-23 11:17:47'),
(160, 10, 53, 65, 80.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:57', '2025-01-23 11:17:47'),
(161, 11, 53, 65, 80.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:57', '2025-01-23 11:17:47'),
(162, 12, 53, 65, 80.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:57', '2025-01-23 11:17:47'),
(163, 13, 53, 65, 80.00, 1, 9, NULL, 9, '2025-01-23 11:17:47', '2025-01-23 11:15:57', '2025-01-23 11:17:47'),
(164, 10, 54, 66, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(165, 11, 54, 66, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(166, 12, 54, 66, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(167, 13, 54, 66, 5.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(168, 10, 55, 66, 80.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(169, 11, 55, 66, 80.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(170, 12, 55, 66, 80.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(171, 13, 55, 66, 80.00, 1, 9, NULL, 9, '2025-01-23 11:17:40', '2025-01-23 11:17:07', '2025-01-23 11:17:40'),
(172, 10, 56, 67, 100.00, 1, 9, NULL, NULL, NULL, '2025-01-23 11:23:28', '2025-01-23 11:23:28'),
(173, 11, 56, 67, 100.00, 1, 9, NULL, NULL, NULL, '2025-01-23 11:23:28', '2025-01-23 11:23:28'),
(174, 12, 56, 67, 100.00, 1, 9, NULL, NULL, NULL, '2025-01-23 11:23:28', '2025-01-23 11:23:28'),
(175, 13, 56, 67, 100.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-23 11:23:28', '2025-02-03 14:06:10'),
(176, 10, 57, 67, 80.00, 1, 9, NULL, NULL, NULL, '2025-01-23 11:23:28', '2025-01-23 11:23:28'),
(177, 11, 57, 67, 80.00, 1, 9, NULL, NULL, NULL, '2025-01-23 11:23:28', '2025-01-23 11:23:28'),
(178, 12, 57, 67, 80.00, 1, 9, NULL, NULL, NULL, '2025-01-23 11:23:28', '2025-01-23 11:23:28'),
(179, 13, 57, 67, 80.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-23 11:23:28', '2025-02-03 14:06:10'),
(180, 10, 58, 63, 20.00, 1, 9, NULL, NULL, NULL, '2025-01-23 12:03:37', '2025-01-23 12:03:37'),
(181, 11, 58, 63, 20.00, 1, 9, NULL, NULL, NULL, '2025-01-23 12:03:37', '2025-01-23 12:03:37'),
(182, 12, 58, 63, 20.00, 1, 9, NULL, NULL, NULL, '2025-01-23 12:03:37', '2025-01-23 12:03:37'),
(183, 13, 58, 63, 20.00, 1, 9, NULL, 9, '2025-02-03 14:06:10', '2025-01-23 12:03:37', '2025-02-03 14:06:10'),
(184, 10, 59, 67, 20.00, 1, 9, NULL, NULL, '2025-01-23 12:34:09', '2025-01-23 12:23:59', '2025-01-23 12:34:09'),
(185, 11, 59, 67, 20.00, 1, 9, NULL, NULL, '2025-01-23 12:34:09', '2025-01-23 12:23:59', '2025-01-23 12:34:09'),
(186, 12, 59, 67, 20.00, 1, 9, NULL, NULL, '2025-01-23 12:34:09', '2025-01-23 12:23:59', '2025-01-23 12:34:09'),
(187, 13, 59, 67, 20.00, 1, 9, NULL, NULL, '2025-01-23 12:34:09', '2025-01-23 12:23:59', '2025-01-23 12:34:09'),
(188, 10, 34, 51, 30.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(189, 10, 35, 52, 12.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(190, 10, 42, 58, 60.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(191, 10, 43, 58, 40.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(192, 10, 44, 58, 30.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(193, 10, 46, 59, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(194, 10, 47, 51, 25.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(195, 10, 48, 52, 20.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(196, 10, 49, 62, 34.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(197, 10, 50, 64, 10.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(198, 10, 51, 64, 80.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(199, 10, 52, 65, 100.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(200, 10, 53, 65, 80.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(201, 10, 54, 66, 5.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(202, 10, 55, 66, 80.00, 1, 9, NULL, NULL, NULL, '2025-01-26 09:19:03', '2025-01-26 09:19:03'),
(203, 10, 61, 82, 11.00, 1, 9, NULL, NULL, NULL, '2025-02-02 09:08:14', '2025-02-02 09:08:14'),
(204, 11, 61, 82, 11.00, 1, 9, NULL, NULL, NULL, '2025-02-02 09:08:14', '2025-02-02 09:08:14'),
(205, 10, 62, 82, 22.00, 1, 9, NULL, NULL, NULL, '2025-02-02 09:08:14', '2025-02-02 09:08:14'),
(206, 11, 62, 82, 22.00, 1, 9, NULL, NULL, NULL, '2025-02-02 09:08:14', '2025-02-02 09:08:14'),
(207, 10, 63, 85, 100.00, 1, 9, NULL, NULL, NULL, '2025-02-03 09:35:19', '2025-02-03 09:35:19');

-- --------------------------------------------------------

--
-- Table structure for table `branch_poses`
--

CREATE TABLE `branch_poses` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `posserial` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pososversion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `posmodelframework` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `presharedkey` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `credential_expired` date DEFAULT NULL,
  `ready_to_submit` tinyint(1) NOT NULL DEFAULT '0',
  `active_from` date DEFAULT NULL,
  `active_to` date DEFAULT NULL,
  `status` enum('active','not_active') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_active',
  `first_authentication` date DEFAULT NULL,
  `last_authentication` date DEFAULT NULL,
  `last_sent_receipt` date DEFAULT NULL,
  `retirement_date` date DEFAULT NULL,
  `permanent_retirement_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branch_recipe`
--

CREATE TABLE `branch_recipe` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `recipe_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branch_times`
--

CREATE TABLE `branch_times` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `day` tinyint(1) DEFAULT NULL,
  `opening_hour` time DEFAULT NULL,
  `closing_hour` time DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `cross_day` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_times`
--

INSERT INTO `branch_times` (`id`, `branch_id`, `day`, `opening_hour`, `closing_hour`, `is_active`, `cross_day`, `created_by`, `modified_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(18, 10, 0, '09:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 07:22:55', '2025-01-26 10:16:08'),
(19, 11, 1, '07:00:00', '20:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:19:25', '2025-01-19 14:29:37'),
(20, 11, 2, '08:00:00', '21:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:19:25', '2025-01-19 14:29:37'),
(21, 11, 3, '08:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:19:25', '2025-01-19 14:29:37'),
(22, 11, 4, '07:00:00', '14:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:19:25', '2025-01-19 14:29:37'),
(23, 11, 5, '07:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:19:25', '2025-01-19 14:29:37'),
(24, 11, 6, '10:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:19:25', '2025-01-19 14:29:38'),
(25, 10, 1, '10:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:25:52', '2025-01-26 10:16:08'),
(26, 10, 2, '04:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:25:52', '2025-01-26 10:16:08'),
(27, 10, 3, '10:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:25:52', '2025-01-26 10:16:08'),
(28, 10, 4, '10:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:25:52', '2025-01-26 10:16:08'),
(29, 10, 6, '10:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:25:52', '2025-01-26 10:16:08'),
(30, 12, 0, '09:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:50:11', '2025-01-26 07:48:35'),
(31, 12, 1, '18:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:50:11', '2025-01-27 13:11:29'),
(32, 12, 2, '10:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:50:11', '2025-01-21 10:31:32'),
(33, 12, 3, '10:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:50:11', '2025-01-21 10:31:32'),
(34, 12, 4, '10:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:50:11', '2025-01-21 10:31:32'),
(35, 12, 5, '10:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:50:11', '2025-01-21 10:31:32'),
(36, 12, 6, '10:00:00', '22:00:00', 1, 1, 9, 9, NULL, NULL, '2025-01-08 08:50:11', '2025-01-21 10:31:32'),
(37, 13, 6, '14:12:00', '02:12:00', 1, 0, 9, 9, 9, '2025-02-03 14:06:10', '2025-01-13 11:12:59', '2025-02-03 14:06:10'),
(38, 13, 2, '00:00:00', '23:59:00', 1, 0, 9, 9, 9, '2025-02-03 14:06:10', '2025-01-14 08:47:14', '2025-02-03 14:06:10'),
(39, 13, 3, '00:00:00', '23:59:00', 1, 0, 9, 9, 9, '2025-02-03 14:06:10', '2025-01-14 08:48:04', '2025-02-03 14:06:10'),
(40, 13, 0, '00:00:00', '23:59:00', 1, 0, 9, 9, 9, '2025-02-03 14:06:10', '2025-01-14 08:51:43', '2025-02-03 14:06:10'),
(41, 13, 1, '00:00:00', '23:59:00', 1, 0, 9, 9, 9, '2025-02-03 14:06:10', '2025-01-14 08:51:56', '2025-02-03 14:06:10'),
(42, 13, 4, '11:00:00', '00:00:00', 1, 1, 9, 9, 9, '2025-02-03 14:06:10', '2025-01-16 07:35:38', '2025-02-03 14:06:10'),
(43, 11, 0, '08:00:00', '20:00:00', 1, 0, 9, 9, NULL, NULL, '2025-01-26 07:48:08', '2025-01-27 12:25:06');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` bigint UNSIGNED NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `logo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name_en`, `name_ar`, `description_en`, `description_ar`, `logo_path`, `is_active`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, 'brand 1', 'ماركة 1', 'brand 1', 'ماركة 1', 'https://erpsystem.testdomain100.online/images/brands/81736413641.png', 1, 1, NULL, NULL, '2025-01-08 09:57:01', '2025-01-08 09:57:01', NULL),
(8, 'description of brand 2', 'علامة 2', 'description of brand 2', 'وصف علامة 2', 'https://erpsystem.testdomain100.online/images/brands/81736413641.png', 1, 9, NULL, NULL, '2025-01-09 08:07:21', '2025-01-09 08:07:27', '2025-01-09 08:07:27');

-- --------------------------------------------------------

--
-- Table structure for table `cashier_machines`
--

CREATE TABLE `cashier_machines` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cashier_machine_logs`
--

CREATE TABLE `cashier_machine_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `cashier_machine_id` bigint UNSIGNED DEFAULT NULL,
  `employee_opening_balance_id` bigint UNSIGNED DEFAULT NULL,
  `deficit_cash` decimal(8,2) DEFAULT '0.00',
  `deficit_visa` decimal(8,2) DEFAULT '0.00',
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL DEFAULT '10',
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_freeze` tinyint(1) NOT NULL DEFAULT '0',
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED NOT NULL,
  `modify_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name_ar`, `name_en`, `description_ar`, `description_en`, `image`, `active`, `code`, `is_freeze`, `parent_id`, `is_deleted`, `created_by`, `modify_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'العهدة', 'First Category', 'عهدة', 'Description of the first category', 'images/categories/0.jpg', 1, '0000', 0, NULL, 1, 1, NULL, NULL, '2025-01-08 06:15:34', '2025-01-08 06:15:34', NULL),
(2, 'هالك', 'Second Category', 'هالك', 'Description of the second category', 'images/categories/1.jpg', 1, '0001', 0, NULL, 1, 1, NULL, NULL, '2025-01-08 06:15:34', '2025-01-08 06:15:34', NULL),
(3, 'منتجات تامة', 'Second Category', 'منتجات تامة', 'Description of the second category', 'images/categories/2.jpg', 1, '0002', 0, NULL, 1, 1, NULL, NULL, '2025-01-08 06:15:34', '2025-01-08 06:15:34', NULL),
(4, 'منتجات', 'Second Category', 'منتجات', 'Description of the second category', 'images/categories/3.jpg', 1, '0003', 0, NULL, 1, 1, NULL, NULL, '2025-01-08 06:15:34', '2025-01-08 06:15:34', NULL),
(5, 'خامات', 'Second Category', 'خامات', 'Description of the second category', 'images/categories/4.jpg', 1, '0004', 0, NULL, 1, 1, NULL, NULL, '2025-01-08 06:15:34', '2025-01-08 06:15:34', NULL),
(14, 'منتج 1', 'Product 1', 'وصف منتج 1', 'description of product 1', 'https://erpsystem.testdomain100.online/images/categories/141736413679.png', 1, '0005', 1, NULL, 1, 9, NULL, NULL, '2025-01-09 08:07:59', '2025-01-09 08:08:32', '2025-01-09 08:08:32');

-- --------------------------------------------------------

--
-- Table structure for table `client_addresses`
--

CREATE TABLE `client_addresses` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longtitude` decimal(10,7) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `address_type` enum('apartment','villa','office') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'apartment',
  `building` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `floor_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apartment_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `street` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_addresses`
--

INSERT INTO `client_addresses` (`id`, `user_id`, `address`, `city`, `state`, `postal_code`, `latitude`, `longtitude`, `is_default`, `is_active`, `created_at`, `updated_at`, `deleted_at`, `address_type`, `building`, `floor_number`, `apartment_number`, `notes`, `country_code`, `address_phone`, `street`) VALUES
(11, 65, 'شارع', 'القاهرة', 'القاهرة', '11611', 30.0603286, 31.2334442, 1, 1, '2025-01-09 11:35:16', '2025-01-09 11:35:27', NULL, 'apartment', 'مبنى', '1', '1', NULL, '+20', '01062501779', NULL),
(23, 70, 'شارع جامعة الدول العربية، ميدان سفنكس، جزيرة ميت عقبة، حي العجوزة، محافظة الجيزة 3752713، مصر', 'محافظة الجيزة', 'محافظة الجيزة', NULL, 30.0604750, 31.2079694, 0, 1, '2025-01-22 08:39:12', '2025-01-22 08:43:04', '2025-01-22 08:43:04', 'apartment', 'مبني', '12', '12', 'علامة', '+20', '01224303330', NULL),
(24, 70, 'ميدان سفنكس', 'Gazirat Mit Oqbah', 'Giza Governorate', '3755140', 30.0627660, 31.2067670, 1, 1, '2025-01-22 08:42:26', '2025-01-23 15:05:51', '2025-01-23 15:05:51', 'office', 'local work', '2', '1', 'جانب فودافون', '+20', '01224303330', NULL),
(33, 24, 'ouo', 'ou', 'ou', NULL, 30.0603286, 31.2334442, 0, 1, '2025-01-27 10:27:15', '2025-01-29 08:46:11', '2025-01-29 08:46:11', 'villa', '4', '5', '8', 'fgh', '+20', '01498754897', NULL),
(34, 24, 'إبن النفيس', 'مدينة الأعلام', 'حي العجوزة', NULL, 30.0635846, 31.2098867, 0, 1, '2025-01-27 10:29:02', '2025-01-29 08:46:11', '2025-01-29 08:46:11', 'office', '١٢ عماره سعيد أبو حوى', '1', '2', 'مترو التوفيقيه', '+20', '01091510571', NULL),
(37, 82, 'ميدان سفنكس', 'Madinet Al Eelam', 'Giza Governorate', '3755201', 30.0616090, 31.2085780, 1, 1, '2025-01-27 11:07:03', '2025-01-27 11:19:24', '2025-01-27 11:19:24', 'office', 'مكتب شاهين', '2', '1', 'جانب فودافون', '+20', '01224304440', NULL),
(39, 39, 'مشير', 'الجيزة', 'الجيزة', NULL, 30.0615845, 31.2086206, 0, 1, '2025-01-27 12:42:55', '2025-01-28 09:51:50', NULL, 'apartment', 'الاسم', '22', '55', NULL, '+20', '01140434525', NULL),
(40, 24, 'عبد المنعم عسران', 'عبد النعيم', 'إمبابة', NULL, 30.0713058, 31.2082526, 0, 1, '2025-01-27 13:37:51', '2025-01-29 08:46:12', '2025-01-29 08:46:12', 'apartment', 'مبنى القوات', '10', '10', 'مترو', '+20', '01091510888', NULL),
(42, 24, 'الغوث', 'مدينة الأعلام', 'حي العجوزة', NULL, 30.0664199, 31.2086985, 0, 1, '2025-01-27 13:45:54', '2025-01-29 08:46:12', '2025-01-29 08:46:12', 'office', 'حى الزمالك مكتب', '10', '5', 'مترو', '+20', '05575688555', NULL),
(43, 24, 'حارة فكري بكري', 'تاج الدول', 'إمبابة', NULL, 30.0740973, 31.2091380, 1, 1, '2025-01-27 13:58:14', '2025-01-29 08:46:12', '2025-01-29 08:46:12', 'apartment', 'شبه', '5', '5', NULL, '+20', '88548855558', NULL),
(44, 87, 'mohandsen', 'Unknown', 'محافظة الجيزة', '3510131', 29.9536889, 31.0973060, 1, 1, '2025-01-27 14:13:27', '2025-01-27 14:23:23', '2025-01-27 14:23:23', 'apartment', 'aa', '2', '1', NULL, '+20', '01224303330', NULL),
(45, 87, 'mohandsen', 'Ad Doqi', 'Giza Governorate', '3751402', 30.0499000, 31.1994000, 0, 1, '2025-01-27 14:19:54', '2025-01-27 14:21:02', '2025-01-27 14:21:02', 'apartment', 'bb', '2', '1', NULL, '+20', '01224303330', NULL),
(48, 33, '1test', 'jj', 'j', 'Unknown', 30.0449792, 31.1721984, 0, 1, '2025-01-27 15:10:13', '2025-01-28 07:52:33', '2025-01-28 07:52:33', 'apartment', 'first', '1', '1', NULL, '+20', '02334564456', NULL),
(49, 33, 'mohandsen', 'Ad Doqi', 'Giza Governorate', '3751402', 30.0499000, 31.1994000, 0, 1, '2025-01-28 07:40:38', '2025-01-28 07:41:18', '2025-01-28 07:41:18', 'apartment', 'bb', '2', '1', NULL, '+20', '01224303330', NULL),
(50, 33, 'المهندسين ميدان سفنكس', 'Gazirat Mit Oqbah', 'Giza Governorate', '3752712', 30.0564656, 31.2034035, 0, 1, '2025-01-28 07:51:03', '2025-01-29 13:40:39', NULL, 'apartment', 'مبني شاهين', 'الدور التاني', 'شقة 1', 'علامة', '+20', '01224303330', NULL),
(51, 33, 'حدائق الاهرام', 'Unknown', 'Giza Governorate', '3285375', 29.9488266, 31.0955012, 0, 1, '2025-01-28 07:59:05', '2025-01-29 13:40:39', NULL, 'apartment', 'عمارة 200', 'الدور الرابع', 'شقة 1', NULL, '+20', '01224303330', NULL),
(52, 33, 'ouo', 'ou', 'ou', NULL, 30.0603286, 31.2334442, 0, 1, '2025-01-28 08:01:22', '2025-01-28 08:16:02', '2025-01-28 08:16:02', 'villa', '4', '5', '8', 'fgh', '+20', '01498754897', NULL),
(53, 33, 'streetname', 'city', 'state', NULL, 30.0603286, 31.2334442, 0, 1, '2025-01-28 08:25:38', '2025-01-28 10:17:48', '2025-01-28 10:17:48', 'apartment', 'ijkl', '1', '2', 'jk', '+20', '54214987547', NULL),
(54, 39, 'عرفات', 'القوصية', 'أسيوط', NULL, 0.0000000, 0.0000000, 1, 1, '2025-01-28 09:51:50', '2025-01-28 09:51:50', NULL, 'apartment', 'فيلا شي', '5', '22', NULL, '+20', '01140434525', NULL),
(55, 33, 'streetname', 'city', 'state', NULL, 30.0603286, 31.2334442, 0, 1, '2025-01-28 09:55:36', '2025-01-28 10:17:54', '2025-01-28 10:17:54', 'apartment', 'ijkl', NULL, '2', 'jk', '+20', '54214987547', NULL),
(56, 33, 'testtt', 'Aâmer', 'Raqqa Governorate', 'Unknown', 36.0603286, 38.2334442, 0, 1, '2025-01-28 10:23:21', '2025-01-29 10:20:52', '2025-01-29 10:20:52', 'villa', '156', NULL, '15', NULL, '+20', '01222222244', NULL),
(57, 33, 'aa', 'city', 'state', '3742202', 30.0603286, 31.2334442, 0, 1, '2025-01-29 10:18:00', '2025-01-29 10:46:59', '2025-01-29 10:46:59', 'villa', '4', '5', '8', 'note', '+20', '01498754897', NULL),
(58, 33, 'streetname', 'city', 'state', NULL, 30.0603286, 31.2334442, 0, 1, '2025-01-29 10:28:07', '2025-01-29 10:28:50', '2025-01-29 10:28:50', 'apartment', 'ijkl', '1', '2', 'jk', '+20', '54214987547', NULL),
(59, 33, 'ميدان سفنكس', 'Unknown', 'محافظة الجيزة', '3510131', 29.9536889, 31.0973060, 1, 1, '2025-01-29 13:40:40', '2025-01-29 13:42:54', NULL, 'office', 'مكتب1', '2', '1', 'جانب فودافون', '+20', '01224303330', NULL),
(60, 88, 'ميدان إبن النفيس', 'AL KIT KAT', 'حي العجوزة', NULL, 30.0647755, 31.2094177, 1, 1, '2025-01-29 13:58:30', '2025-01-29 13:58:30', NULL, 'villa', 'فيلا', NULL, '10', 'مترو', '+20', '01091510571', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hexa_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`id`, `name_ar`, `name_en`, `hexa_code`, `created_at`, `updated_at`, `created_by`, `deleted_by`, `deleted_at`, `modified_by`) VALUES
(2, 'ابيض', 'WHITE', 'FFFFFF', '2024-11-21 11:07:27', '2024-11-21 11:14:31', 13, NULL, NULL, 13),
(5, 'اسود', 'Black', '#0c0c0d', '2024-12-18 10:34:34', '2024-12-18 10:44:32', NULL, NULL, NULL, NULL),
(7, 'ازرق', 'Blue', '#0000FF', '2024-12-22 06:41:38', '2024-12-24 07:13:14', 1, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency_symbol` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `job_years` int DEFAULT '1',
  `phone_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `length` int DEFAULT NULL,
  `flag` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name_ar`, `name_en`, `code`, `currency_ar`, `currency_en`, `currency_code`, `currency_symbol`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `deleted_by`, `modified_by`, `job_years`, `phone_code`, `length`, `flag`) VALUES
('0498b51a-3227-4112-91cf-eaa190c8b230', 'البرازيل', 'Brazil', 'BR', 'ريال برازيلي', 'Brazilian Real', 'BRL', 'R$', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+55', 11, NULL),
('0dd2f951-2657-4a4d-997b-133045013456', 'المكسيك', 'Mexico', 'MX', 'بيزو مكسيكي', 'Mexican Peso', 'MXN', '$', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+52', 10, NULL),
('0fc4637b-d8f3-4244-93aa-409d567110b9', 'الولايات المتحدة', 'United States', 'US', 'دولار أمريكي', 'US Dollar', 'USD', '$', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+1', 10, NULL),
('13887fec-2d9f-4a39-be58-b381b668f940', 'أستراليا', 'Australia', 'AU', 'دولار أسترالي', 'Australian Dollar', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+61', 9, NULL),
('1b9034ea-3b35-4b11-88a0-8142c8fd6762', 'فرنسا', 'France', 'FR', 'يورو', 'Euro', 'EUR', '€', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+33', 9, NULL),
('1fb41224-2a6e-4456-9dcd-05c270465237', 'الكويت', 'Kuwait', 'KW', 'دينار كويتي', 'Kuwaiti Dinar', 'KWD', 'د.ك', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+965', 8, NULL),
('32fdf8e1-707d-4047-b728-51aa7c896487', 'روسيا', 'Russia', 'RU', 'روبل روسي', 'Russian Ruble', 'RUB', '₽', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+7', 10, NULL),
('64525842-e619-4626-8269-9acf204c965b', 'الصين', 'China', 'CN', 'يوان صيني', 'Chinese Yuan', 'CNY', '¥', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+86', 11, NULL),
('8bcf66f5-63a0-47ae-91c7-07d865334d50', 'المملكة المتحدة', 'United Kingdom', 'GB', 'جنيه إسترليني', 'British Pound', 'GBP', '£', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+44', 10, NULL),
('92a3b4ec-76a2-4f81-9328-9283c1289a0c', 'الإمارات', 'United Arab Emirates', 'AE', 'درهم إماراتي', 'Emirati Dirham', 'AED', 'د.إ', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+971', 9, NULL),
('9e80453d-561f-4f8c-8f99-865fd4ef8ecd', 'مصر', 'Egypt', 'EG', 'جنيه مصري', 'Egyptian Pound', 'EGP', '£', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+20', 11, NULL),
('a630a044-4b9c-4623-abf7-5bb15402b83f', 'كوريا الجنوبية', 'South Korea', 'KR', 'وون كوري', 'South Korean Won', 'KRW', '₩', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+82', 10, NULL),
('b72cf60b-1fc9-4573-8674-6dee8c909ad8', 'كندا', 'Canada', 'CA', 'دولار كندي', 'Canadian Dollar', 'CAD', '$', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+1', 10, NULL),
('dc93fbe7-5024-4544-a994-cfbdc127e9c9', 'الهند', 'India', 'IN', 'روبية هندية', 'Indian Rupee', 'INR', '₹', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+91', 10, NULL),
('dd01a3d9-2f75-4a39-8e02-49b495672b07', 'ألمانيا', 'Germany', 'DE', 'يورو', 'Euro', 'EUR', '€', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+49', 10, NULL),
('e9db95b5-8c02-4c59-83c3-5fd729c40582', 'إيطاليا', 'Italy', 'IT', 'يورو', 'Euro', 'EUR', '€', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+39', 10, NULL),
('f499b829-38d1-43cc-bf2c-55743c8146bd', 'اليابان', 'Japan', 'JP', 'ين ياباني', 'Japanese Yen', 'JPY', '¥', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+81', 10, NULL),
('fa7fda60-e3a7-4a08-ab07-dee052bce359', 'السعودية', 'Saudi Arabia', 'SA', 'ريال سعودي', 'Saudi Riyal', 'SAR', '﷼', NULL, NULL, NULL, NULL, NULL, NULL, 1, '+966', 9, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('percentage','fixed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(8,2) NOT NULL,
  `minimum_spend` decimal(8,2) DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `count_usage` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `minimum_spend`, `usage_limit`, `start_date`, `end_date`, `is_active`, `created_by`, `modified_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`, `count_usage`) VALUES
(11, '12345', 'percentage', 10.00, 10.00, 10, '2024-12-31 16:16:00', '2025-01-08 16:16:00', 0, 1, NULL, NULL, NULL, '2025-01-08 12:16:26', '2025-01-09 10:00:02', 0),
(12, 'SAVE25', 'percentage', 25.00, 50.00, 20, '2025-01-01 13:42:00', '2025-01-31 13:42:00', 1, 9, 1, NULL, NULL, '2025-01-13 10:42:38', '2025-01-27 08:17:18', 0),
(13, '111', 'fixed', 10.00, 130.00, 2, '2025-01-14 13:05:00', '2025-01-31 13:05:00', 1, 1, 9, NULL, NULL, '2025-01-14 10:05:14', '2025-01-29 11:50:24', 2),
(14, 'SAVE30', 'fixed', 30.00, 100.00, 10, '2025-01-25 08:52:00', '2025-01-31 08:52:00', 1, 9, 9, NULL, NULL, '2025-01-26 07:52:39', '2025-01-29 14:05:55', 0);

-- --------------------------------------------------------

--
-- Table structure for table `cuisines`
--

CREATE TABLE `cuisines` (
  `id` bigint UNSIGNED NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cuisines`
--

INSERT INTO `cuisines` (`id`, `name_en`, `name_ar`, `description_en`, `description_ar`, `is_active`, `image_path`, `created_by`, `modified_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(4, 'itailian', 'ايطالي', NULL, NULL, 1, 'images/cuisines/1736344151_dish.jpg', 1, 72, NULL, NULL, '2025-01-08 11:49:11', '2025-01-27 07:47:35'),
(5, 'egyptian', 'مصري', NULL, NULL, 1, 'images/cuisines/1736344186_dish.jpg', 1, 9, NULL, NULL, '2025-01-08 11:49:46', '2025-01-15 13:25:03'),
(6, 'new', 'new', 'new', 'new', 1, 'images/cuisines/1736753050_logo-with-white-bg.png', 9, NULL, NULL, '2025-01-13 06:24:46', '2025-01-13 06:24:10', '2025-01-13 06:24:46'),
(7, 'a', 'a', 'description', 'وصف', 1, NULL, 9, NULL, 9, '2025-01-19 10:43:22', '2025-01-19 10:43:18', '2025-01-19 10:43:22'),
(8, 'ايطالي', 'italian', 'ok', 'ok', 1, 'cuisines/OAZQUdNWMEcxIP6u0dET3HXRVfoRDXJtgf8xrD8Q.jpg', 1, 1, 9, '2025-01-21 09:10:59', '2025-01-21 09:09:42', '2025-01-21 09:10:59'),
(9, 'egyption', 'مصري', 'description', 'تفاصيل', 1, 'images/cuisines/1737447137_emy-qGOADX9b3Hg-unsplash.jpg', 1, 1, 9, '2025-01-22 07:52:12', '2025-01-21 09:12:17', '2025-01-22 07:52:12'),
(10, 'a', 'a', 'AA', 'AA', 1, 'images/cuisines/1737528790_logo-with-white-bg.png', 9, 9, 9, '2025-01-22 07:53:32', '2025-01-22 07:53:10', '2025-01-22 07:53:32');

-- --------------------------------------------------------

--
-- Table structure for table `delays`
--

CREATE TABLE `delays` (
  `id` bigint UNSIGNED NOT NULL,
  `time_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delay_deductions`
--

CREATE TABLE `delay_deductions` (
  `id` bigint UNSIGNED NOT NULL,
  `delay_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `deduction_amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delay_times`
--

CREATE TABLE `delay_times` (
  `id` bigint UNSIGNED NOT NULL,
  `time` int NOT NULL,
  `type` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1 for minutes, 2 for hours',
  `punishment_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `punishment_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_settings`
--

CREATE TABLE `delivery_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` double(8,2) DEFAULT NULL,
  `longtitude` double(8,2) DEFAULT NULL,
  `radius` double(8,2) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT '0.00',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint UNSIGNED NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` bigint UNSIGNED NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('percentage','fixed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(8,2) NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discounts`
--

INSERT INTO `discounts` (`id`, `name_en`, `name_ar`, `type`, `value`, `start_date`, `end_date`, `is_active`, `created_by`, `modified_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(24, 'discount', 'خصم', 'percentage', 10.00, '2025-01-01 16:15:00', '2025-01-31 16:15:00', 1, 1, 9, NULL, NULL, '2025-01-08 12:15:32', '2025-01-21 11:00:05');

-- --------------------------------------------------------

--
-- Table structure for table `dishes`
--

CREATE TABLE `dishes` (
  `id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `cuisine_id` bigint UNSIGNED DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `has_sizes` tinyint(1) NOT NULL DEFAULT '0',
  `has_addon` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dishes`
--

INSERT INTO `dishes` (`id`, `category_id`, `cuisine_id`, `price`, `image`, `name_en`, `description_en`, `description_ar`, `name_ar`, `is_active`, `has_sizes`, `has_addon`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `code`) VALUES
(46, 16, 5, 10.00, 'http://erp.test/images/dishes/461738592121.PNG', 'مجبوس لحم عربي ضاني طازج', 'عيش مع لحم ضاني يقدم مع معبوج أخضر ومعبوج أحمر ومرق باميه او دقوس', 'عيش مع لحم ضاني يقدم مع معبوج أخضر ومعبوج أحمر ومرق باميه او دقوس', 'مجبوس لحم عربي ضاني طازج', 1, 0, 0, 1, 9, NULL, '2025-01-16 12:00:04', '2025-02-03 14:15:21', NULL, '0001'),
(48, 16, 4, 20.00, 'https://erpsystem.testdomain100.online/images/dishes/481737025267.jpg', 'burger', 'burger', 'برجر', 'برجر', 1, 0, 0, 1, 9, NULL, '2025-01-16 12:01:07', '2025-01-23 12:49:54', NULL, '0002'),
(51, 16, 4, NULL, 'https://erpsystem.testdomain100.online/images/dishes/511737025343.jpg', 'burger with sizes', 'burger with sizes', 'برجر احجام', 'برجر احجام', 1, 1, 0, 1, 9, NULL, '2025-01-16 12:02:23', '2025-01-22 11:06:22', NULL, '0003'),
(52, 16, 4, 12.00, 'https://erpsystem.testdomain100.online/images/dishes/511737025343.jpg', 'burger combo', 'burger combo', 'برجر كومبو', 'برجر كومبو', 1, 1, 1, 1, 9, NULL, '2025-01-16 12:03:29', '2025-01-21 10:55:50', NULL, '0004'),
(53, 16, 4, 5.00, 'https://erpsystem.testdomain100.online/images/dishes/521737025915.jpg', 'water', NULL, NULL, 'مياة', 1, 0, 0, 1, NULL, NULL, '2025-01-16 12:11:09', '2025-01-16 12:11:09', NULL, '0005'),
(54, 16, 4, 50.00, 'https://elkoot.testdomain100.online/images/dishes/541737452955.jpg', 'برياني دجاج', 'عيش مع قطع دجاج وصوص بهارات وبصل وخيار وروب', 'عيش مع قطع دجاج وصوص بهارات وبصل وخيار وروب', 'برياني دجاج', 1, 0, 1, 1, 9, NULL, '2025-01-16 12:22:19', '2025-01-21 10:49:15', NULL, '0006'),
(55, 16, 5, 50.00, 'https://elkoot.testdomain100.online/images/dishes/551737452554.jpg', 'مموش ربيان', 'عيش مع ربيان طري يقدم مع دقوس ومعبوج أخضر ومعبوج أحمر', 'عيش مع ربيان طري يقدم مع دقوس ومعبوج أخضر ومعبوج أحمر', 'مموش ربيان', 1, 1, 0, 9, 9, NULL, '2025-01-16 12:30:21', '2025-01-21 10:42:34', NULL, '0007'),
(56, 16, 4, 50.00, 'https://elkoot.testdomain100.online/images/dishes/561737452903.jpg', 'كبسة بريه لحم عربي', 'عيش مع لحم ضاني يقدم مع معبوج أخضر ومعبوج أحمر ومرق باميه او دقوس', 'عيش مع لحم ضاني يقدم مع معبوج أخضر ومعبوج أحمر ومرق باميه او دقوس', 'كبسة بريه لحم عربي', 1, 1, 1, 9, 9, NULL, '2025-01-16 12:32:17', '2025-01-21 10:48:24', NULL, '0008'),
(57, 17, 5, 50.00, 'https://elkoot.testdomain100.online/images/dishes/571737453755.crdownload', 'cake', 'chocolate cake', 'كيكة الشكولاتة', 'كيك', 1, 0, 1, 9, 9, NULL, '2025-01-16 14:29:24', '2025-01-22 07:42:54', NULL, '0009'),
(58, 17, 5, 10.00, NULL, 'test', 'description', 'وصف', 'تجربة طبق', 1, 1, 1, 9, 9, 9, '2025-01-19 09:03:52', '2025-01-20 14:02:47', '2025-01-20 14:02:47', '0010'),
(59, 16, 4, 99.00, NULL, 'test2', 'desc', 'desc', '2تجربة', 1, 1, 1, 9, NULL, 9, '2025-01-19 10:09:47', '2025-01-19 10:31:08', '2025-01-19 10:31:08', '0011'),
(60, 16, 5, 50.00, NULL, 'pasta with addons', 'desc', 'وصف', 'باستا اضافات', 1, 0, 1, 9, 9, NULL, '2025-01-20 10:06:26', '2025-01-20 10:09:30', NULL, '0012'),
(61, 17, 5, 930.00, 'https://erpsystem.testdomain100.online/images/dishes/611737545145.png', 'Marcia Mullen', 'Doloribus occaecat d', 'Sit quaerat quo in', 'Galvin Franklin', 1, 0, 0, 1, NULL, 9, '2025-01-22 12:25:45', '2025-01-22 12:51:21', '2025-01-22 12:51:21', '0013'),
(62, 17, 4, 273.00, 'https://erpsystem.testdomain100.online/images/dishes/621737545232.png', 'Charles Weeks', 'Ipsam perferendis fa', 'Accusamus dolor odio', 'Erica Walton', 0, 1, 1, 1, NULL, 9, '2025-01-22 12:27:12', '2025-01-22 12:51:29', '2025-01-22 12:51:29', '0014'),
(63, 16, 4, 40.00, 'https://erpsystem.testdomain100.online/images/dishes/631737631224.jpg', 'rice', 'desc', 'وصف', 'ارز', 1, 1, 1, 9, 9, NULL, '2025-01-23 11:07:56', '2025-01-23 12:41:24', NULL, '0015'),
(64, 16, 4, NULL, 'https://erpsystem.testdomain100.online/images/dishes/641737627268.png', 'potatoes', 'aaa', 'aaa', 'بطاطس', 1, 1, 1, 9, NULL, 9, '2025-01-23 11:14:27', '2025-01-23 11:17:55', '2025-01-23 11:17:55', '0016'),
(65, 16, 4, NULL, 'https://erpsystem.testdomain100.online/images/dishes/651737627356.png', 'potatoes', 'aa', 'aa', 'بطاطس', 1, 1, 1, 9, NULL, 9, '2025-01-23 11:15:56', '2025-01-23 11:17:47', '2025-01-23 11:17:47', '0017'),
(66, 16, 4, NULL, NULL, 'potatoes', 'aa', 'aa', 'بطاطس', 1, 1, 1, 9, NULL, 9, '2025-01-23 11:17:06', '2025-01-23 11:17:40', '2025-01-23 11:17:40', '0018'),
(67, 16, 5, NULL, 'https://erpsystem.testdomain100.online/images/dishes/671737637753.jpg', 'potatoes', 'Fried potatoes', 'بطاطس مقلية', 'بطاطس', 1, 1, 1, 9, 9, NULL, '2025-01-23 11:23:27', '2025-01-23 14:09:13', NULL, '0019'),
(68, 16, 4, 20.00, NULL, 'new', 'aa', 'aa', 'new', 1, 0, 0, 9, NULL, 9, '2025-01-26 11:24:09', '2025-01-26 12:45:39', '2025-01-26 12:45:39', '0020'),
(69, 16, 4, 20.00, NULL, 'new', 'aaa', 'aaa', 'new', 1, 0, 0, 9, 9, 9, '2025-01-26 11:26:36', '2025-01-26 12:45:47', '2025-01-26 12:45:47', '0021'),
(71, 16, 4, 20.00, NULL, 'test', 'aa', 'aa', 'test', 1, 0, 0, 9, NULL, 9, '2025-01-26 11:27:52', '2025-01-26 12:45:56', '2025-01-26 12:45:56', '0022'),
(72, 16, 4, 15.00, NULL, 'test', 'aaa', 'aa', 'test', 1, 0, 0, 9, NULL, 9, '2025-01-29 08:50:10', '2025-01-29 08:50:37', '2025-01-29 08:50:37', '0023'),
(73, 16, 4, NULL, NULL, 'test', 'a', 'a', 'test', 1, 1, 0, 9, NULL, 9, '2025-01-29 08:52:22', '2025-01-29 08:52:43', '2025-01-29 08:52:43', '0024'),
(74, 16, 4, 15.00, NULL, 'test', 'a', 'a', 'test', 1, 0, 0, 9, NULL, 9, '2025-01-29 08:53:09', '2025-01-29 09:09:34', '2025-01-29 09:09:34', '0025'),
(75, 16, 4, 5.00, NULL, 'aa', 'aaaaaaaaaaa', 'aa', 'aa', 1, 0, 0, 9, NULL, 9, '2025-01-29 09:01:20', '2025-01-29 09:03:15', '2025-01-29 09:03:15', '0026'),
(76, 16, 4, 20.00, NULL, 'aa', 'aa', 'aa', 'aa', 1, 0, 1, 9, NULL, 9, '2025-01-29 09:07:17', '2025-01-29 09:09:26', '2025-01-29 09:09:26', '0027'),
(78, 16, 5, 950.00, 'https://erpsystem.testdomain100.online/images/dishes/781738152044.png', 'Jacob Strong', 'Provident in fugiat', 'Magni dicta enim dol', 'Kathleen Vaughn', 1, 0, 0, 1, NULL, NULL, '2025-01-29 13:00:44', '2025-01-29 13:00:44', NULL, '0028'),
(80, NULL, NULL, 171.00, 'https://erpsystem.testdomain100.online/images/addons/801738156179.png', 'Hunter Carson', 'Sed eum consequatur', 'Nostrum quidem volup', 'Ruby Cline', 1, 0, 0, NULL, NULL, 1, '2025-01-29 14:09:39', '2025-01-29 14:09:46', '2025-01-29 14:09:46', '0029'),
(82, 16, 4, NULL, 'http://erp.test/images/dishes/821738487294.PNG', 'rr', 'rr', 'rr', 'rr', 1, 1, 1, 9, NULL, NULL, '2025-02-02 09:08:14', '2025-02-02 09:08:14', NULL, '0030'),
(83, 16, 4, NULL, 'http://erp.test/images/dishes/831738574517.PNG', 'new2', 'new2', 'new2', 'new2', 1, 0, 0, 9, NULL, NULL, '2025-02-03 09:21:57', '2025-02-03 09:21:57', NULL, '0031'),
(84, 16, 4, NULL, 'http://erp.test/images/dishes/841738574703.PNG', 'new3', 'new3', 'new3', 'new3', 1, 0, 0, 9, NULL, NULL, '2025-02-03 09:25:03', '2025-02-03 09:25:03', NULL, '0032'),
(85, 16, 4, NULL, NULL, 'طبق', 'طبق', 'طبق', 'طبق', 1, 1, 0, 9, NULL, NULL, '2025-02-03 09:35:19', '2025-02-03 09:35:19', NULL, '0033'),
(86, 16, 4, 135.00, NULL, 'طبق', 'طبق', 'طبق', 'طبق', 1, 0, 0, 9, 9, 9, '2025-02-03 09:35:47', '2025-02-04 10:57:22', '2025-02-04 10:57:22', '0034');

-- --------------------------------------------------------

--
-- Table structure for table `dish_addons`
--

CREATE TABLE `dish_addons` (
  `id` bigint UNSIGNED NOT NULL,
  `dish_id` bigint UNSIGNED NOT NULL,
  `addon_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `price` decimal(10,2) DEFAULT NULL,
  `addon_category_id` bigint UNSIGNED DEFAULT NULL,
  `min_addons` int UNSIGNED NOT NULL DEFAULT '0',
  `max_addons` int UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dish_addons`
--

INSERT INTO `dish_addons` (`id`, `dish_id`, `addon_id`, `quantity`, `price`, `addon_category_id`, `min_addons`, `max_addons`, `deleted_at`, `created_at`, `updated_at`) VALUES
(34, 52, 15, 1, 10.00, 1, 1, 5, NULL, '2025-01-16 12:03:29', '2025-01-22 11:08:29'),
(35, 54, 15, 1, 11.00, 1, 1, 2, NULL, '2025-01-16 12:22:19', '2025-01-22 11:09:05'),
(36, 56, 15, 1, 20.00, 1, 1, 2, NULL, '2025-01-16 12:32:17', '2025-01-16 12:32:17'),
(37, 56, 16, 1, 15.00, 1, 1, 2, NULL, '2025-01-16 12:32:17', '2025-01-16 12:32:17'),
(38, 56, 18, 1, 20.00, 1, 1, 2, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(39, 56, 34, 1, 5.00, 1, 1, 2, NULL, '2025-01-16 12:32:18', '2025-01-16 12:32:18'),
(40, 57, 15, 1, 5.00, 1, 0, 3, '2025-01-22 07:38:18', '2025-01-16 14:29:24', '2025-01-22 07:38:18'),
(41, 57, 16, 1, 5.00, 1, 0, 3, NULL, '2025-01-16 14:29:24', '2025-01-16 14:29:24'),
(42, 57, 18, 1, 5.00, 1, 0, 3, '2025-01-22 07:38:18', '2025-01-16 14:29:24', '2025-01-22 07:38:18'),
(43, 57, 34, 1, 5.00, 1, 0, 3, '2025-01-22 07:38:18', '2025-01-16 14:29:25', '2025-01-22 07:38:18'),
(44, 58, 15, 1, 30.00, 1, 1, 3, NULL, '2025-01-19 09:03:52', '2025-01-19 10:06:58'),
(45, 58, 16, 1, 5.00, 1, 1, 3, NULL, '2025-01-19 09:03:52', '2025-01-19 09:03:52'),
(46, 58, 34, 1, 10.00, 1, 1, 3, '2025-01-19 09:56:33', '2025-01-19 09:48:36', '2025-01-19 09:56:33'),
(47, 58, 18, 1, 15.00, 1, 1, 3, NULL, '2025-01-19 09:48:36', '2025-01-19 09:48:36'),
(48, 58, 15, 1, 20.00, 2, 1, 3, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(49, 58, 16, 1, 5.00, 2, 1, 3, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(50, 58, 18, 1, 15.00, 2, 1, 3, '2025-01-19 10:06:58', '2025-01-19 09:59:38', '2025-01-19 10:06:58'),
(51, 59, 15, 12, 10.00, 1, 1, 3, NULL, '2025-01-19 10:09:47', '2025-01-19 10:09:47'),
(52, 59, 34, 10, 5.00, 1, 1, 3, NULL, '2025-01-19 10:09:47', '2025-01-19 10:09:47'),
(53, 60, 15, 1, 10.00, 1, 1, 2, '2025-01-20 10:10:03', '2025-01-20 10:06:26', '2025-01-20 10:10:03'),
(54, 60, 15, 1, 10.00, 1, 1, 2, '2025-01-20 10:10:03', '2025-01-20 10:06:26', '2025-01-20 10:10:03'),
(55, 60, 15, 1, 10.00, 1, 1, 2, NULL, '2025-01-20 10:06:26', '2025-01-20 10:06:26'),
(56, 60, 16, 1, 10.00, 1, 1, 2, NULL, '2025-01-20 10:09:30', '2025-01-20 10:09:30'),
(57, 60, 18, 1, 10.00, 1, 1, 2, NULL, '2025-01-20 10:09:30', '2025-01-20 10:09:30'),
(58, 52, 16, 1, 15.00, 1, 1, 5, NULL, '2025-01-22 11:08:29', '2025-01-22 11:08:29'),
(59, 52, 18, 1, 10.00, 1, 1, 5, NULL, '2025-01-22 11:08:29', '2025-01-22 11:08:29'),
(60, 52, 34, 1, 15.00, 1, 1, 5, NULL, '2025-01-22 11:08:29', '2025-01-22 11:08:29'),
(61, 54, 16, 1, 12.00, 1, 1, 2, NULL, '2025-01-22 11:09:05', '2025-01-22 11:09:05'),
(62, 54, 18, 1, 13.00, 1, 1, 2, NULL, '2025-01-22 11:09:05', '2025-01-22 11:09:05'),
(63, 62, 15, 1, 12.97, 1, 1, 4, NULL, '2025-01-22 12:27:12', '2025-01-22 12:27:12'),
(64, 64, 15, 1, 10.00, 1, 1, 4, NULL, '2025-01-23 11:14:28', '2025-01-23 11:14:28'),
(65, 64, 15, 2, 20.00, 1, 1, 4, NULL, '2025-01-23 11:14:28', '2025-01-23 11:14:28'),
(66, 65, 15, 1, 5.00, 1, 1, 3, NULL, '2025-01-23 11:15:56', '2025-01-23 11:15:56'),
(67, 65, 16, 2, 5.00, 1, 1, 3, NULL, '2025-01-23 11:15:56', '2025-01-23 11:15:56'),
(68, 65, 18, 3, 5.00, 1, 1, 3, NULL, '2025-01-23 11:15:56', '2025-01-23 11:15:56'),
(69, 66, 15, 1, 10.00, 1, 1, 4, NULL, '2025-01-23 11:17:06', '2025-01-23 11:17:06'),
(70, 66, 15, 2, 20.00, 1, 1, 4, NULL, '2025-01-23 11:17:06', '2025-01-23 11:17:06'),
(71, 67, 15, 1, 15.00, 1, 0, 2, '2025-01-23 13:13:53', '2025-01-23 11:23:27', '2025-01-23 13:13:53'),
(72, 67, 15, 1, 15.00, 1, 0, 2, '2025-01-23 13:13:53', '2025-01-23 11:23:27', '2025-01-23 13:13:53'),
(73, 63, 34, 1, 5.00, 1, 0, 2, NULL, '2025-01-23 12:06:18', '2025-01-23 12:06:18'),
(74, 63, 16, 1, 10.00, 1, 0, 2, NULL, '2025-01-23 12:06:18', '2025-01-23 12:06:18'),
(75, 67, 15, 1, 15.00, 2, 0, 2, '2025-01-23 13:24:20', '2025-01-23 13:13:28', '2025-01-23 13:24:20'),
(76, 67, 15, 1, 15.00, 1, 2, 5, NULL, '2025-01-23 13:23:31', '2025-01-23 14:02:47'),
(77, 67, 16, 5, 20.00, 1, 2, 5, NULL, '2025-01-23 13:23:31', '2025-01-23 14:02:47'),
(78, 67, 15, 1, 15.00, 2, 0, 2, '2025-01-23 13:27:00', '2025-01-23 13:25:50', '2025-01-23 13:27:00'),
(79, 67, 16, 2, 10.00, 2, 0, 2, '2025-01-23 13:27:00', '2025-01-23 13:25:50', '2025-01-23 13:27:00'),
(80, 67, 15, 1, 15.00, 2, 0, 2, '2025-01-23 13:27:42', '2025-01-23 13:27:00', '2025-01-23 13:27:42'),
(81, 67, 16, 2, 10.00, 2, 0, 2, '2025-01-23 13:27:42', '2025-01-23 13:27:01', '2025-01-23 13:27:42'),
(82, 67, 15, 1, 15.00, 2, 0, 2, '2025-01-23 13:35:44', '2025-01-23 13:34:16', '2025-01-23 13:35:44'),
(83, 67, 16, 2, 10.00, 2, 0, 2, '2025-01-23 13:35:44', '2025-01-23 13:34:16', '2025-01-23 13:35:44'),
(84, 67, 15, 1, 15.00, 2, 0, 2, '2025-01-23 13:38:00', '2025-01-23 13:36:01', '2025-01-23 13:38:00'),
(85, 67, 16, 2, 10.00, 2, 0, 2, '2025-01-23 13:38:00', '2025-01-23 13:36:01', '2025-01-23 13:38:00'),
(86, 67, 34, 2, 12.00, 1, 2, 5, '2025-01-23 14:04:11', '2025-01-23 14:03:26', '2025-01-23 14:04:11'),
(87, 67, 18, 5, 20.00, 1, 2, 5, '2025-01-23 14:06:00', '2025-01-23 14:05:38', '2025-01-23 14:06:00'),
(88, 82, 15, 1, 11.00, 1, 1, 1, NULL, '2025-02-02 09:08:14', '2025-02-02 09:08:14');

-- --------------------------------------------------------

--
-- Table structure for table `dish_categories`
--

CREATE TABLE `dish_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dish_categories`
--

INSERT INTO `dish_categories` (`id`, `name_en`, `name_ar`, `description_en`, `description_ar`, `parent_id`, `image_path`, `is_active`, `created_by`, `modified_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(16, 'main dish', 'اطباق رئيسية', NULL, NULL, NULL, 'https://erpsystem.testdomain100.online/images/dishes/511737025343.jpg', 1, 1, NULL, NULL, NULL, '2025-01-16 11:56:18', '2025-01-16 11:56:18'),
(17, 'dessert', 'حلويات', NULL, NULL, NULL, 'https://erpsystem.testdomain100.online/images/dish_category/OIP (3).jpg', 1, 1, 9, NULL, NULL, '2025-01-16 11:58:04', '2025-01-22 11:21:37'),
(18, 'a', 'a', 'description', 'وصف', NULL, 'https://erpsystem.testdomain100.online/images/dish...', 1, 9, NULL, 9, '2025-01-19 10:44:13', '2025-01-19 10:44:07', '2025-01-19 10:44:13'),
(19, 'a', 'a', 'desc', 'وصف', NULL, 'https://erpsystem.testdomain100.online/images/dish...', 1, 9, 9, 9, '2025-01-20 13:53:29', '2025-01-20 13:50:28', '2025-01-20 13:53:29'),
(20, 'aa', 'aa', 'aa', 'aa', NULL, 'https://erpsystem.testdomain100.online/images/dish_category/201737985517.png', 0, 9, 9, NULL, NULL, '2025-01-27 14:45:17', '2025-01-27 14:45:36'),
(21, 'ss', 'dd', 'dd', 'cff', NULL, 'https://erpsystem.testdomain100.online/images/dish_category/211738046671.png', 1, 1, NULL, NULL, NULL, '2025-01-28 07:44:31', '2025-01-28 07:44:31');

-- --------------------------------------------------------

--
-- Table structure for table `dish_details`
--

CREATE TABLE `dish_details` (
  `id` bigint UNSIGNED NOT NULL,
  `dish_id` bigint UNSIGNED NOT NULL,
  `dish_size_id` bigint UNSIGNED DEFAULT NULL,
  `recipe_id` bigint UNSIGNED NOT NULL,
  `quantity` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dish_details`
--

INSERT INTO `dish_details` (`id`, `dish_id`, `dish_size_id`, `recipe_id`, `quantity`, `created_at`, `updated_at`) VALUES
(51, 46, NULL, 17, 1.00, '2025-01-16 12:00:04', '2025-01-16 12:00:04'),
(52, 48, NULL, 29, 1.00, '2025-01-16 12:01:07', '2025-01-16 12:01:07'),
(53, 51, 34, 17, 1.00, '2025-01-16 12:02:23', '2025-01-16 12:02:23'),
(54, 52, 35, 17, 1.00, '2025-01-16 12:03:29', '2025-01-16 12:03:29'),
(55, 53, NULL, 37, 1.00, '2025-01-16 12:11:10', '2025-01-16 12:11:10'),
(56, 54, NULL, 17, 1.00, '2025-01-16 12:22:19', '2025-01-16 12:22:19'),
(57, 55, 36, 17, 3.00, '2025-01-16 12:30:21', '2025-01-16 12:30:21'),
(58, 55, 37, 17, 2.00, '2025-01-16 12:30:21', '2025-01-16 12:30:21'),
(59, 55, 38, 17, 1.00, '2025-01-16 12:30:21', '2025-01-16 12:30:21'),
(60, 57, 41, 17, 2.00, '2025-01-16 14:29:24', '2025-01-16 14:29:24'),
(61, 58, 42, 17, 1.00, '2025-01-19 09:03:52', '2025-01-19 09:03:52'),
(62, 58, 43, 17, 1.00, '2025-01-19 09:03:52', '2025-01-19 09:03:52'),
(63, 58, 44, 17, 1.00, '2025-01-19 09:03:52', '2025-01-19 09:03:52'),
(64, 58, NULL, 17, 4.00, '2025-01-19 09:48:36', '2025-01-19 09:48:36'),
(65, 59, 46, 17, 1.00, '2025-01-19 10:09:47', '2025-01-19 10:09:47'),
(66, 60, NULL, 17, 1.00, '2025-01-20 10:06:26', '2025-01-20 10:06:26'),
(67, 57, NULL, 17, 2.00, '2025-01-22 07:42:24', '2025-01-22 07:42:24'),
(68, 51, NULL, 17, 1.00, '2025-01-22 11:06:22', '2025-01-22 11:06:22'),
(69, 52, NULL, 17, 1.00, '2025-01-22 11:08:29', '2025-01-22 11:08:29'),
(70, 61, NULL, 33, 885.00, '2025-01-22 12:25:45', '2025-01-22 12:25:45'),
(71, 62, 49, 17, 1.00, '2025-01-22 12:27:12', '2025-01-22 12:27:12'),
(72, 63, NULL, 17, 1.00, '2025-01-23 11:07:56', '2025-01-23 12:03:36'),
(73, 64, 50, 17, 1.00, '2025-01-23 11:14:27', '2025-01-23 11:14:27'),
(74, 64, 51, 17, 1.00, '2025-01-23 11:14:28', '2025-01-23 11:14:28'),
(75, 65, 52, 17, 1.00, '2025-01-23 11:15:56', '2025-01-23 11:15:56'),
(76, 65, 53, 17, 1.00, '2025-01-23 11:15:56', '2025-01-23 11:15:56'),
(77, 66, 54, 17, 1.00, '2025-01-23 11:17:06', '2025-01-23 11:17:06'),
(78, 66, 55, 17, 1.00, '2025-01-23 11:17:06', '2025-01-23 11:17:06'),
(79, 67, 56, 17, 1.00, '2025-01-23 11:23:27', '2025-01-23 11:23:27'),
(80, 67, 57, 17, 1.00, '2025-01-23 11:23:27', '2025-01-23 11:23:27'),
(81, 63, NULL, 33, 10.00, '2025-01-23 11:24:28', '2025-01-23 11:24:28'),
(82, 63, NULL, 36, 2.00, '2025-01-23 12:00:12', '2025-01-23 12:00:12'),
(83, 67, NULL, 17, 1.00, '2025-01-23 12:23:59', '2025-01-23 12:23:59'),
(84, 68, NULL, 52, 1.00, '2025-01-26 11:24:09', '2025-01-26 11:24:09'),
(85, 69, NULL, 17, 1.00, '2025-01-26 11:26:37', '2025-01-26 11:26:37'),
(86, 69, NULL, 29, 1.00, '2025-01-26 11:26:56', '2025-01-26 11:26:56'),
(87, 71, NULL, 54, 1.00, '2025-01-26 11:27:53', '2025-01-26 11:27:53'),
(88, 72, NULL, 55, 5.00, '2025-01-29 08:50:10', '2025-01-29 08:50:10'),
(89, 73, 60, 17, 1.00, '2025-01-29 08:52:22', '2025-01-29 08:52:22'),
(90, 74, NULL, 17, 5.00, '2025-01-29 08:53:09', '2025-01-29 08:53:09'),
(91, 76, NULL, 17, 1.00, '2025-01-29 09:07:17', '2025-01-29 09:07:17'),
(93, 78, NULL, 17, 361.00, '2025-01-29 13:00:44', '2025-01-29 13:00:44'),
(94, 79, NULL, 56, 1.00, '2025-01-29 13:01:07', '2025-01-29 13:01:07'),
(95, 80, NULL, 57, 1.00, '2025-01-29 14:09:39', '2025-01-29 14:09:39'),
(96, 82, 61, 17, 1.00, '2025-02-02 09:08:14', '2025-02-02 09:08:14'),
(97, 82, 62, 17, 2.00, '2025-02-02 09:08:14', '2025-02-02 09:08:14'),
(98, 83, NULL, 17, 1.00, '2025-02-03 09:21:57', '2025-02-03 09:21:57'),
(99, 84, NULL, 17, 1.00, '2025-02-03 09:25:03', '2025-02-03 09:25:03'),
(100, 85, 63, 17, 1.00, '2025-02-03 09:35:19', '2025-02-03 09:35:19'),
(101, 86, NULL, 17, 1.00, '2025-02-03 09:35:47', '2025-02-03 09:35:47');

-- --------------------------------------------------------

--
-- Table structure for table `dish_discount`
--

CREATE TABLE `dish_discount` (
  `id` bigint UNSIGNED NOT NULL,
  `dish_id` bigint UNSIGNED NOT NULL,
  `discount_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modify_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dish_discount`
--

INSERT INTO `dish_discount` (`id`, `dish_id`, `discount_id`, `created_at`, `updated_at`, `created_by`, `modify_by`, `deleted_by`, `deleted_at`) VALUES
(92, 46, 24, '2025-01-19 07:40:52', '2025-01-21 10:15:02', 9, NULL, NULL, '2025-01-21 10:15:02');

-- --------------------------------------------------------

--
-- Table structure for table `dish_sizes`
--

CREATE TABLE `dish_sizes` (
  `id` bigint UNSIGNED NOT NULL,
  `dish_id` bigint UNSIGNED NOT NULL,
  `size_name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size_name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `default_size` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dish_sizes`
--

INSERT INTO `dish_sizes` (`id`, `dish_id`, `size_name_en`, `size_name_ar`, `price`, `default_size`, `created_at`, `updated_at`, `deleted_at`) VALUES
(34, 51, 'large', 'كبير', 30.00, 1, '2025-01-16 12:02:23', '2025-01-22 11:06:22', NULL),
(35, 52, 'small', 'صغير', 12.00, 0, '2025-01-16 12:03:29', '2025-01-22 11:08:29', NULL),
(36, 55, 'large', 'كبير', 70.00, 0, '2025-01-16 12:30:21', '2025-01-22 11:12:21', NULL),
(37, 55, 'medium', 'وسط', 60.00, 1, '2025-01-16 12:30:21', '2025-01-22 11:12:21', NULL),
(38, 55, 'small', 'صغير', 50.00, 0, '2025-01-16 12:30:21', '2025-01-22 11:12:21', NULL),
(39, 56, 'large', 'كبير', 70.00, 0, '2025-01-16 12:32:17', '2025-01-21 12:24:46', NULL),
(40, 56, 'medium', 'وسط', 60.00, 1, '2025-01-16 12:32:17', '2025-01-21 12:24:46', NULL),
(41, 57, 'large', 'كبير', 99.00, 0, '2025-01-16 14:29:24', '2025-01-22 07:42:54', NULL),
(42, 58, 'large', 'كبير', 60.00, 0, '2025-01-19 09:03:52', '2025-01-19 10:06:58', NULL),
(43, 58, 'medium', 'وسط', 40.00, 0, '2025-01-19 09:03:52', '2025-01-19 10:06:58', NULL),
(44, 58, 'small', 'صغير', 30.00, 0, '2025-01-19 09:03:52', '2025-01-19 10:06:58', NULL),
(45, 58, 'family size', 'عائلي', 100.00, 0, '2025-01-19 09:48:36', '2025-01-19 10:06:58', '2025-01-19 10:06:58'),
(46, 59, 'large', 'كبير', 10.00, 1, '2025-01-19 10:09:47', '2025-01-19 10:09:47', NULL),
(47, 51, 'medium', 'وسط', 25.00, 0, '2025-01-22 11:06:22', '2025-01-22 11:06:22', NULL),
(48, 52, 'Large', 'كبير', 20.00, 1, '2025-01-22 11:08:29', '2025-01-22 11:08:29', NULL),
(49, 62, 'we', 'er', 34.00, 1, '2025-01-22 12:27:12', '2025-01-22 12:27:12', NULL),
(50, 64, 'aa', 'aa', 10.00, 1, '2025-01-23 11:14:27', '2025-01-23 11:14:27', NULL),
(51, 64, 'medium', 'وسط', 80.00, 0, '2025-01-23 11:14:28', '2025-01-23 11:14:28', NULL),
(52, 65, 'large', 'كبير', 100.00, 1, '2025-01-23 11:15:56', '2025-01-23 11:15:56', NULL),
(53, 65, 'medium', 'وسط', 80.00, 0, '2025-01-23 11:15:56', '2025-01-23 11:15:56', NULL),
(54, 66, 'large', 'كبير', 5.00, 1, '2025-01-23 11:17:06', '2025-01-23 11:17:06', NULL),
(55, 66, 'medium', 'وسط', 80.00, 0, '2025-01-23 11:17:06', '2025-01-23 11:17:06', NULL),
(56, 67, 'large', 'كبير', 100.00, 1, '2025-01-23 11:23:27', '2025-01-23 14:09:13', NULL),
(57, 67, 'medium', 'وسط', 80.00, 0, '2025-01-23 11:23:27', '2025-01-23 14:09:13', NULL),
(58, 63, 'large', 'كبير', 20.00, 1, '2025-01-23 12:03:36', '2025-01-23 12:41:24', NULL),
(59, 67, 'small', 'صغير', 30.00, 1, '2025-01-23 12:23:59', '2025-01-23 12:34:08', '2025-01-23 12:34:08'),
(60, 73, 'large', 'كبير', 20.00, 1, '2025-01-29 08:52:22', '2025-01-29 08:52:22', NULL),
(61, 82, 'أحجام الطبق', 'أحجام الطبق', 11.00, 1, '2025-02-02 09:08:14', '2025-02-02 09:08:14', NULL),
(62, 82, 'أحجام الطبق2', 'أحجام الطبق2', 22.00, 0, '2025-02-02 09:08:14', '2025-02-02 09:08:14', NULL),
(63, 85, 'أحجام الطبق', 'أحجام الطبق', 100.00, 1, '2025-02-03 09:35:19', '2025-02-03 09:35:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `divisions`
--

CREATE TABLE `divisions` (
  `id` bigint UNSIGNED NOT NULL,
  `line_id` bigint UNSIGNED NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `domains`
--

CREATE TABLE `domains` (
  `id` int UNSIGNED NOT NULL,
  `domain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tenant_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `domains`
--

INSERT INTO `domains` (`id`, `domain`, `tenant_id`, `created_at`, `updated_at`) VALUES
(1, 'new.localhost', 'new', '2024-11-18 11:38:08', '2024-11-18 11:38:08');

-- --------------------------------------------------------

--
-- Table structure for table `einvoices`
--

CREATE TABLE `einvoices` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `public_urls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `cancel_request_date` date DEFAULT NULL,
  `reject_request_date` date DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `validation_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `validation_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `error_msg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `invoice_type` enum('i','c') NOT NULL DEFAULT 'i',
  `reference_invoice_id` bigint UNSIGNED DEFAULT NULL,
  `expired_on` date DEFAULT NULL,
  `reject_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancel_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `einvoices`
--

INSERT INTO `einvoices` (`id`, `invoice_id`, `uuid`, `status`, `public_urls`, `cancel_request_date`, `reject_request_date`, `submission_date`, `validation_ar`, `validation_en`, `error_msg`, `invoice_type`, `reference_invoice_id`, `expired_on`, `reject_reason`, `cancel_reason`, `created_at`, `updated_at`) VALUES
(1, '12', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '#/header.uuid: StringTooLong', 'i', NULL, NULL, NULL, NULL, NULL, NULL),
(2, '20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 08:24:19', '2025-02-02 08:24:19'),
(3, '21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 08:26:39', '2025-02-02 08:26:39'),
(4, '22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 08:40:56', '2025-02-02 08:40:56'),
(5, '23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 08:42:33', '2025-02-02 08:42:33'),
(6, '24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 08:43:14', '2025-02-02 08:43:14'),
(7, '25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 09:40:48', '2025-02-02 09:40:48'),
(8, '26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 09:40:53', '2025-02-02 09:40:53'),
(9, '27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 09:43:16', '2025-02-02 09:43:16'),
(10, '28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 12:24:04', '2025-02-02 12:24:04'),
(11, '29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 12:28:14', '2025-02-02 12:28:14'),
(12, '30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 12:29:37', '2025-02-02 12:29:37'),
(13, '31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 12:30:19', '2025-02-02 12:30:19'),
(14, '32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 12:30:30', '2025-02-02 12:30:30'),
(15, '33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 12:52:26', '2025-02-02 12:52:26'),
(16, '34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-02 13:43:53', '2025-02-02 13:43:53'),
(17, '35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-03 14:10:56', '2025-02-03 14:10:56'),
(18, '36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i', NULL, NULL, NULL, NULL, '2025-02-03 14:12:34', '2025-02-03 14:12:34');

-- --------------------------------------------------------

--
-- Table structure for table `einvoice_settings`
--

CREATE TABLE `einvoice_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `einvoice_settings`
--

INSERT INTO `einvoice_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'لا كوتشينا للضيافه', NULL, NULL),
(2, 'tax_issuer_id', '675170532', NULL, NULL),
(3, 'tax_activity', '5610', NULL, NULL),
(4, 'tax_item_code_type', 'EGS', NULL, NULL),
(5, 'idSrvBaseUrlPreprodEg', 'https://id.preprod.eta.gov.eg', NULL, NULL),
(6, 'idSrvBaseUrlProdEg', 'https://id.eta.gov.eg', NULL, NULL),
(7, 'apiBaseUrlPreprodEg', 'https://api.preprod.invoicing.eta.gov.eg', NULL, NULL),
(8, 'apiBaseUrlProdEg', 'https://api.invoicing.eta.gov.eg', NULL, NULL),
(9, 'tax_client_id', 'f3aa0a8a-a908-468a-91ac-31bfc4d688c0', NULL, NULL),
(10, 'tax_secret_id', 'b27a84da-8ff4-4aca-8749-a83693fc2ec0', NULL, NULL),
(11, 'tax_live', '0', NULL, NULL),
(12, 'tax_client_id_live', 'f20ebe87-aced-4fc1-9929-860ded30d1ed', NULL, NULL),
(13, 'tax_secret_id_live', '108784b9-5234-4db7-b7de-06d6320634e4', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `national_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marital_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `blood_group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_relationship` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality_id` bigint UNSIGNED DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `position_id` bigint UNSIGNED DEFAULT NULL,
  `supervisor_id` bigint UNSIGNED DEFAULT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `daily_excuse_hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `monthly_excuse_hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `assurance_salary` decimal(10,2) DEFAULT NULL,
  `assurance_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employment_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_biometric` tinyint(1) NOT NULL DEFAULT '0',
  `biometric_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flag` enum('waiter','chef','cashier','call_center','customer_service','driver','kitchen manager','branch manager','kitchen staff','supervisor','employee') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_code`, `user_id`, `first_name`, `last_name`, `email`, `country_code`, `phone_number`, `gender`, `birth_date`, `national_id`, `passport_number`, `marital_status`, `blood_group`, `emergency_contact_name`, `emergency_contact_relationship`, `emergency_contact_phone`, `address_en`, `address_ar`, `nationality_id`, `department_id`, `position_id`, `supervisor_id`, `branch_id`, `hire_date`, `salary`, `daily_excuse_hours`, `monthly_excuse_hours`, `assurance_salary`, `assurance_number`, `bank_account`, `employment_type`, `status`, `notes`, `is_biometric`, `biometric_id`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `password`, `flag`) VALUES
(3, '2', 67, 'احمد', 'محمد', 'aaa@mail.com', '+20', '01224303330', 'male', '2025-01-01', '11', '12', 'Single', 'A+', '111', 'aa', '0122', NULL, NULL, 1, NULL, NULL, NULL, 10, '2025-01-01', 111.00, 0.00, 0.00, 122.00, '14', '15', 'Part-Time', 'active', NULL, 1, '16', 9, 9, NULL, '2025-01-13 08:26:44', '2025-01-29 13:34:04', NULL, NULL, 'waiter'),
(4, '1', 71, 'محمد', 'حسن', 'mohamed@email.com', '+20', '01224303330', NULL, NULL, '12', '13', 'Single', 'A+', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11, NULL, NULL, 0.00, 0.00, NULL, '15', '16', NULL, 'active', NULL, 1, '17', 9, 9, NULL, '2025-01-21 14:53:18', '2025-01-22 15:04:23', NULL, NULL, 'waiter'),
(5, '454454', NULL, 'super', 'visor', 'supervisor@admin.com', '+20', '01000416715', 'male', '1998-01-02', '2452424', '35454551', 'Single', 'AB+', 'ewe', 'ew', '224552452', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 23232.00, 0.00, 0.00, 241514.00, '213284', '24522', 'Full-Time', 'active', NULL, 1, '74231254', 9, NULL, NULL, '2025-01-23 13:13:40', '2025-01-23 13:13:40', NULL, NULL, 'supervisor'),
(6, '4544547', 72, 'kitchen', 'manager', 'kitchenmanager@admin.com', '+20', '01245785647', NULL, '0987-12-06', '2452445', '3545412', 'Single', NULL, 'ewe', 'ew', '224552435', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-23', 23232.00, 0.00, 0.00, 2415154.00, '2132847', '2452245', 'Full-Time', 'active', NULL, 1, '742312454', 9, NULL, NULL, '2025-01-23 13:15:50', '2025-01-23 13:21:37', NULL, NULL, 'employee'),
(8, '454478', 74, 'kitchen', 'manager1', 'kitchenmanager1@admin.com', '+20', '01000416747', 'male', '1998-01-03', '2452447', '35454554', 'Single', 'AB+', 'ewe', 'ew', '22455245', NULL, NULL, 1, NULL, NULL, 5, NULL, NULL, 588.00, 0.00, 0.00, 247458.00, '21325', '245225', 'Full-Time', 'active', NULL, 1, '74231255', 9, NULL, NULL, '2025-01-23 13:21:37', '2025-01-23 13:21:37', NULL, NULL, 'kitchen manager');

-- --------------------------------------------------------

--
-- Table structure for table `employee_floor_partitions`
--

CREATE TABLE `employee_floor_partitions` (
  `id` bigint UNSIGNED NOT NULL,
  `floor_partition_id` bigint UNSIGNED DEFAULT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `date` date DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_opening_balances`
--

CREATE TABLE `employee_opening_balances` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `cashier_machine_id` bigint UNSIGNED DEFAULT NULL,
  `employee_schedule_id` bigint UNSIGNED DEFAULT NULL,
  `open_cash` decimal(8,2) DEFAULT '0.00',
  `open_visa` decimal(8,2) DEFAULT '0.00',
  `close_cash` decimal(8,2) DEFAULT '0.00',
  `close_visa` decimal(8,2) DEFAULT '0.00',
  `real_cash` decimal(8,2) DEFAULT '0.00',
  `real_visa` decimal(8,2) DEFAULT '0.00',
  `deficit_cash` decimal(8,2) DEFAULT '0.00',
  `deficit_visa` decimal(8,2) DEFAULT '0.00',
  `type` int NOT NULL DEFAULT '1' COMMENT '1 for open, 2 for close',
  `date` date NOT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_schedules`
--

CREATE TABLE `employee_schedules` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `shift_id` bigint UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `excuses`
--

CREATE TABLE `excuses` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `excuse_request_id` bigint UNSIGNED DEFAULT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `requested_time` time DEFAULT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approver_id` bigint UNSIGNED DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `excuse_requests`
--

CREATE TABLE `excuse_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_date` date DEFAULT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `excuse_settings`
--

CREATE TABLE `excuse_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `max_daily_hours` int DEFAULT '4',
  `max_monthly_hours` int DEFAULT '12',
  `before_request_period` int NOT NULL DEFAULT '1',
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `floors`
--

CREATE TABLE `floors` (
  `id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1 for in door, 2 for out door, 3 for both',
  `smoking` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1 smokin, 2 not smokin, 3 both',
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `floors`
--

INSERT INTO `floors` (`id`, `name_ar`, `name_en`, `type`, `smoking`, `modified_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`, `branch_id`, `created_by`) VALUES
(2, 'الدور الاول', 'floor one', '3', '1', 1, NULL, NULL, '2025-01-08 08:51:01', '2025-01-08 08:51:10', 10, 1);

-- --------------------------------------------------------

--
-- Table structure for table `floor_partitions`
--

CREATE TABLE `floor_partitions` (
  `id` bigint UNSIGNED NOT NULL,
  `floor_id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacity` int NOT NULL DEFAULT '1',
  `type` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1 on door, 2 out door',
  `smoking` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1 smokin, 2 not smokin',
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `f_a_q_s`
--

CREATE TABLE `f_a_q_s` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `question_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `question_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `answer_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `answer_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `f_a_q_s`
--

INSERT INTO `f_a_q_s` (`id`, `name_ar`, `name_en`, `question_ar`, `question_en`, `answer_ar`, `answer_en`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `active`) VALUES
('112db528-9840-47d0-b5cd-f8d232592ae3', 'new', 'new', '<p>hhh</p>', '<p>hhh</p>', '<p>hh</p>', '<p>hh</p>', 1, NULL, NULL, '2024-12-29 08:49:10', '2024-12-29 08:49:46', '2024-12-29 08:49:46', 1),
('75abf368-30db-49fb-b9d1-f6ee00b1eee6', 'تجربة', 'Name', '<p>سؤال 1</p>', '<p>Question 1</p>', '<p>اجابة 1</p>', '<p>Answer 1</p>', 1, 9, NULL, '2024-12-30 05:48:01', '2025-01-15 09:13:12', NULL, 1),
('fc1cf49b-c440-4968-b10b-ed347b60772e', 'test', 'test', '<div class=\"card-header bg-white border-bottom \">\r\n<h5 class=\"card-title fw-bold\">أين بإمكاني تغيير معلومات حسابي؟</h5>\r\n</div>\r\n<div class=\"card-body \">&nbsp;</div>', '<p>Can I change my account information?</p>', '<div class=\"card mt-4 p-4  \">\r\n<div class=\"card-body \">\r\n<p class=\"text-muted\">حالما تقوم بتسجيل الدخول إلى التطبيق باستخدام رقم الجوال الخاص بك، اضغط على حسابي من القائمة العلوية، حيث بإمكانك تغيير معلومات حسابك من هناك</p>\r\n</div>\r\n</div>\r\n<div class=\"card mt-4 p-4  \">\r\n<div class=\"card-header bg-white border-bottom \">&nbsp;</div>\r\n</div>', '<p>Once you log in to the app using your mobile number, click on My Account from the top menu, where you can change your account information from there.</p>', 9, 1, NULL, '2024-12-26 12:51:27', '2024-12-29 08:49:23', '2024-12-29 08:49:23', 1);

-- --------------------------------------------------------

--
-- Table structure for table `gifts`
--

CREATE TABLE `gifts` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gifts`
--

INSERT INTO `gifts` (`id`, `name`, `expiration_date`, `created_at`, `updated_at`) VALUES
(1, 'Welcome Gift', '2025-10-30', '2025-01-08 06:18:13', '2025-01-08 06:18:13');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` bigint UNSIGNED NOT NULL,
  `recipe_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `product_unit_id` bigint UNSIGNED NOT NULL,
  `quantity` decimal(8,2) NOT NULL,
  `loss_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `recipe_id`, `product_id`, `product_unit_id`, `quantity`, `loss_percent`, `deleted_at`, `created_at`, `updated_at`) VALUES
(19, 15, 20, 4, 1.00, 10.00, '2025-01-13 10:53:57', '2025-01-08 11:50:21', '2025-01-13 10:53:57'),
(20, 16, 20, 4, 1.00, 10.00, '2025-01-13 10:52:52', '2025-01-08 11:51:38', '2025-01-13 10:52:52'),
(21, 17, 20, 4, 1.00, 10.00, '2025-01-13 10:55:52', '2025-01-08 11:52:17', '2025-01-13 10:55:52'),
(22, 29, 21, 16, 1.00, 11.00, NULL, '2025-01-13 09:55:08', '2025-01-22 13:10:15'),
(23, 30, 22, 6, 1.00, 0.00, '2025-01-13 10:56:14', '2025-01-13 10:52:04', '2025-01-13 10:56:14'),
(24, 16, 22, 6, 1.00, 10.00, '2025-01-13 10:54:06', '2025-01-13 10:52:52', '2025-01-13 10:54:06'),
(25, 15, 21, 5, 1.00, 10.00, '2025-01-21 07:50:01', '2025-01-13 10:53:57', '2025-01-21 07:50:01'),
(26, 16, 21, 5, 1.00, 10.00, NULL, '2025-01-13 10:54:06', '2025-01-13 10:54:06'),
(27, 18, 21, 5, 1.00, 10.00, '2025-01-21 11:04:18', '2025-01-13 10:54:19', '2025-01-21 11:04:18'),
(28, 22, 22, 6, 6.00, 10.00, '2025-01-13 10:56:27', '2025-01-13 10:54:54', '2025-01-13 10:56:27'),
(29, 17, 21, 16, 1.00, 10.00, NULL, '2025-01-13 10:55:52', '2025-01-21 07:49:48'),
(30, 30, 21, 5, 1.00, 0.00, '2025-01-13 10:57:05', '2025-01-13 10:56:14', '2025-01-13 10:57:05'),
(31, 32, 23, 7, 1.00, 10.00, '2025-01-13 11:02:15', '2025-01-13 11:01:48', '2025-01-13 11:02:15'),
(32, 33, 24, 8, 1.00, 0.00, '2025-01-14 11:01:50', '2025-01-13 11:07:39', '2025-01-14 11:01:50'),
(33, 34, 25, 9, 5.00, 10.00, '2025-01-22 11:30:43', '2025-01-14 10:59:26', '2025-01-22 11:30:43'),
(34, 33, 25, 9, 10.00, 0.00, '2025-01-15 12:59:54', '2025-01-14 11:01:50', '2025-01-15 12:59:54'),
(35, 35, 21, 5, 10.00, 5.00, '2025-01-15 12:09:22', '2025-01-15 12:09:07', '2025-01-15 12:09:22'),
(36, 35, 21, 5, 10.00, 5.00, '2025-01-15 12:09:45', '2025-01-15 12:09:22', '2025-01-15 12:09:45'),
(37, 35, 21, 5, 10.00, 5.00, '2025-01-15 12:09:58', '2025-01-15 12:09:45', '2025-01-15 12:09:58'),
(38, 35, 21, 5, 10.00, 5.00, '2025-01-15 12:11:48', '2025-01-15 12:09:58', '2025-01-15 12:11:48'),
(39, 35, 21, 5, 10.00, 5.00, '2025-01-15 12:12:09', '2025-01-15 12:11:48', '2025-01-15 12:12:09'),
(40, 35, 21, 5, 10.00, 5.00, '2025-01-15 12:12:21', '2025-01-15 12:12:09', '2025-01-15 12:12:21'),
(41, 35, 21, 5, 10.00, 5.00, '2025-01-15 12:12:37', '2025-01-15 12:12:21', '2025-01-15 12:12:37'),
(42, 35, 21, 5, 10.00, 5.00, '2025-01-16 07:21:16', '2025-01-15 12:12:37', '2025-01-16 07:21:16'),
(43, 33, 25, 9, 10.00, 0.00, '2025-01-15 13:00:10', '2025-01-15 12:59:54', '2025-01-15 13:00:10'),
(44, 33, 25, 9, 10.00, 0.00, '2025-01-15 13:00:18', '2025-01-15 13:00:11', '2025-01-15 13:00:18'),
(45, 33, 25, 9, 10.00, 0.00, '2025-01-15 13:22:52', '2025-01-15 13:00:18', '2025-01-15 13:22:52'),
(46, 33, 25, 9, 10.00, 0.00, NULL, '2025-01-15 13:22:52', '2025-01-15 13:22:52'),
(47, 36, 25, 9, 1.00, 0.00, NULL, '2025-01-15 15:18:17', '2025-01-15 15:18:17'),
(48, 35, 21, 5, 10.00, 0.00, '2025-01-19 10:38:34', '2025-01-16 07:21:16', '2025-01-19 10:38:34'),
(49, 37, 25, 16, 1.00, 0.00, NULL, '2025-01-16 12:11:10', '2025-01-20 13:55:26'),
(50, 38, 21, 16, 1.00, 0.00, '2025-01-20 13:54:01', '2025-01-19 10:39:21', '2025-01-20 13:54:01'),
(51, 39, 21, 16, 736.00, 89.00, '2025-01-19 14:57:47', '2025-01-19 14:41:51', '2025-01-19 14:57:47'),
(52, 40, 21, 5, 1.00, 0.00, '2025-01-20 13:43:45', '2025-01-20 13:43:37', '2025-01-20 13:43:45'),
(53, 40, 21, 5, 1.00, 0.00, '2025-01-20 13:43:56', '2025-01-20 13:43:45', '2025-01-20 13:43:56'),
(54, 40, 21, 5, 1.00, 0.00, '2025-01-20 13:44:06', '2025-01-20 13:43:56', '2025-01-20 13:44:06'),
(55, 41, 21, 16, 1.00, 0.00, '2025-01-20 13:54:23', '2025-01-20 13:53:56', '2025-01-20 13:54:23'),
(56, 37, 21, 16, 2.00, 0.00, '2025-01-20 13:55:35', '2025-01-20 13:55:26', '2025-01-20 13:55:35'),
(57, 15, 21, 5, 1.00, 10.00, NULL, '2025-01-21 07:50:01', '2025-01-21 07:50:01'),
(58, 18, 21, 5, 1.00, 10.00, NULL, '2025-01-21 11:04:18', '2025-01-21 11:04:18'),
(59, 42, 21, 16, 5.00, 0.00, '2025-01-21 12:09:09', '2025-01-21 12:08:37', '2025-01-21 12:09:09'),
(60, 43, 25, 9, 489.00, 72.00, '2025-01-22 11:29:48', '2025-01-22 11:29:42', '2025-01-22 11:29:48'),
(61, 34, 25, 9, 15.00, 10.00, NULL, '2025-01-22 11:30:43', '2025-01-22 11:30:43'),
(62, 44, 21, 5, 0.03, 0.03, '2025-01-22 12:11:59', '2025-01-22 11:38:04', '2025-01-22 12:11:59'),
(63, 44, 21, 5, 0.03, 0.03, '2025-01-22 12:12:10', '2025-01-22 12:11:59', '2025-01-22 12:12:10'),
(64, 44, 21, 5, 0.03, 0.03, '2025-01-22 12:13:46', '2025-01-22 12:12:10', '2025-01-22 12:13:46'),
(65, 48, 25, 9, 788.00, 46.00, '2025-01-22 12:12:39', '2025-01-22 12:12:25', '2025-01-22 12:12:39'),
(66, 48, 25, 9, 788.00, 46.00, '2025-01-22 12:13:42', '2025-01-22 12:12:39', '2025-01-22 12:13:42'),
(67, 49, 25, 9, 741.00, 26.00, '2025-01-22 12:13:59', '2025-01-22 12:13:54', '2025-01-22 12:13:59'),
(68, 50, 21, 16, 77.00, 0.00, '2025-01-26 14:17:32', '2025-01-22 13:09:18', '2025-01-26 14:17:32'),
(69, 51, 21, 16, 55.00, 0.00, '2025-01-22 13:38:29', '2025-01-22 13:25:57', '2025-01-22 13:38:29'),
(70, 51, 26, 16, 44.00, 0.00, '2025-01-22 13:38:29', '2025-01-22 13:25:57', '2025-01-22 13:38:29'),
(71, 52, 25, 9, 1.00, 0.00, '2025-01-27 14:34:51', '2025-01-26 11:24:09', '2025-01-27 14:34:51'),
(72, 54, 25, 9, 1.00, 0.00, '2025-01-27 14:34:46', '2025-01-26 11:27:53', '2025-01-27 14:34:46'),
(73, 50, 21, 5, 77.00, 0.00, '2025-01-26 14:17:41', '2025-01-26 14:17:32', '2025-01-26 14:17:41'),
(74, 55, 26, 16, 1.00, 0.00, '2025-01-29 08:50:51', '2025-01-29 08:49:42', '2025-01-29 08:50:51'),
(75, 56, 25, 9, 1.00, 0.00, NULL, '2025-01-29 13:01:07', '2025-01-29 13:01:07'),
(76, 57, 25, 9, 1.00, 0.00, NULL, '2025-01-29 14:09:39', '2025-01-29 14:09:39');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kitchen_logs`
--

CREATE TABLE `kitchen_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `order_details_id` bigint UNSIGNED DEFAULT NULL,
  `dish_id` bigint UNSIGNED DEFAULT NULL,
  `dish_size_id` bigint UNSIGNED DEFAULT NULL,
  `offer_id` bigint UNSIGNED DEFAULT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `store_id` bigint UNSIGNED DEFAULT NULL,
  `order_addone_id` bigint UNSIGNED DEFAULT NULL,
  `dish_addone_id` bigint UNSIGNED DEFAULT NULL,
  `table_id` bigint UNSIGNED DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `quantity` int DEFAULT '0',
  `status` enum('pending','completed','cancelled','inprogress') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `order_type` enum('Delivery','CallCenter','Takeaway','Online','InResturant') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'InResturant',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_nationals`
--

CREATE TABLE `leave_nationals` (
  `id` bigint UNSIGNED NOT NULL,
  `country_id` bigint UNSIGNED NOT NULL,
  `leave_type_id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `leave_type_id` bigint UNSIGNED DEFAULT NULL,
  `date` date DEFAULT NULL,
  `from` date DEFAULT NULL,
  `to` date DEFAULT NULL,
  `leave_count` int DEFAULT NULL,
  `resone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stauts` int DEFAULT '1' COMMENT '1 not yet,2 current,3 exceed',
  `agreement` int DEFAULT '1' COMMENT '1 not agree,2 agree',
  `agreement_by` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `agreement_date` date DEFAULT NULL,
  `agreement_resone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_settings`
--

CREATE TABLE `leave_settings` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leave_type_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_leave` int DEFAULT NULL,
  `max_leave` int DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name_ar`, `name_en`, `created_by`, `modified_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
('28f4156b-fb6d-4edb-8f45-caa6c2ae9def', 'اجازات اعتيادية', 'Ordinary leaves', 1, NULL, NULL, NULL, '2025-01-08 06:18:26', NULL),
('2eebbd2d-427b-4d1f-bb81-cc8c8b3cd08f', 'اجازات رسمية', 'Official leaves', 1, NULL, NULL, NULL, '2025-01-08 06:18:26', NULL),
('7d7c9ae9-42ae-46ff-84c4-aa27c7013b89', 'اجازات طارئة', 'Emergency leaves', 1, NULL, NULL, NULL, '2025-01-08 06:18:26', NULL),
('f5f0883c-1e67-421a-97ba-5064889a1aa0', 'اجازات سنوية', 'Annual leaves', 1, NULL, NULL, NULL, '2025-01-08 06:18:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lines`
--

CREATE TABLE `lines` (
  `id` bigint UNSIGNED NOT NULL,
  `store_id` bigint UNSIGNED NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_freeze` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logos`
--

CREATE TABLE `logos` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `logos`
--

INSERT INTO `logos` (`id`, `name_ar`, `name_en`, `image`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('03d7c99b-0ba0-4ed7-be74-ce58d9ce1822', 'تست', 'Test', 'https://erpsystem.testdomain100.online/images/logos/1734866211.jpg', 1, 1, NULL, '2024-12-22 10:16:51', '2024-12-22 10:21:14', '2024-12-22 10:21:14'),
('076e9a61-71c9-4856-84de-2100449bfc17', 'تست', 'Test', 'https://erpsystem.testdomain100.online/images/logos/1734866487.jpg', 1, 1, NULL, '2024-12-22 10:21:27', '2024-12-29 08:27:52', '2024-12-29 08:27:52');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2016_06_01_000001_create_oauth_auth_codes_table', 1),
(2, '2016_06_01_000002_create_oauth_access_tokens_table', 1),
(3, '2016_06_01_000003_create_oauth_refresh_tokens_table', 1),
(4, '2016_06_01_000004_create_oauth_clients_table', 1),
(5, '2016_06_01_000005_create_oauth_personal_access_clients_table', 1),
(6, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(7, '2024_09_11_063954_create_password_reset_tokens_table', 1),
(8, '2024_09_11_063955_create_countries_table', 1),
(9, '2024_09_11_063956_create_users_table', 1),
(10, '2024_09_11_063957_create_categories_table', 1),
(11, '2024_09_11_063958_create_units_table', 1),
(12, '2024_09_11_063959_create_products_table', 1),
(13, '2024_09_11_063960_create_colors_table', 1),
(14, '2024_09_11_064361_create_sizes_table', 1),
(15, '2024_09_11_064362_create_product_images_table', 1),
(16, '2024_09_11_064363_create_product_units_table', 1),
(17, '2024_09_11_064364_create_product_sizes_table', 1),
(18, '2024_09_11_064365_create_product_colors_table', 1),
(19, '2024_09_11_064366_create_notifications_table', 1),
(20, '2024_09_11_064367_create_vendors_table', 1),
(21, '2024_09_11_064368_create_branches_table', 1),
(22, '2024_09_11_064369_create_stores_table', 1),
(23, '2024_09_11_064370_create_opening_balances_table', 1),
(24, '2024_09_11_064371_create_store_transactions_table', 1),
(25, '2024_09_11_064372_create_store_transaction_details_table', 1),
(26, '2024_09_11_064373_create_product_transactions_table', 1),
(27, '2024_09_11_064374_create_lines_table', 1),
(28, '2024_09_11_064375_create_divisions_table', 1),
(29, '2024_09_11_064376_create_shelves_table', 1),
(30, '2024_09_11_064377_create_actionbacklog_table', 1),
(31, '2024_09_11_064378_create_api_codes_table', 1),
(32, '2024_09_12_064379_update_notifications_table', 1),
(33, '2024_09_12_064380_add_columns_colors_table', 1),
(34, '2024_09_12_064381_add_columns_sizes_table', 1),
(35, '2024_09_12_064382_add_columns_countries_table', 1),
(36, '2024_09_12_064383_add_columns_opening_balance_table', 1),
(37, '2024_09_16_064384_add_column_price_opening_balance_table', 1),
(38, '2024_09_17_064385_add_soft_deletes_to_branches_table', 1),
(39, '2024_09_17_064386_add_soft_deletes_to_stores_table', 1),
(40, '2024_09_17_064387_add_deleted_at_to_actionbacklog_table', 1),
(41, '2024_09_17_064388_add_deleted_at_to_api_codes_table', 1),
(42, '2024_09_17_064389_add_deleted_at_to_categories_table', 1),
(43, '2024_09_17_064390_add_deleted_at_to_notifications_table', 1),
(44, '2024_09_17_064391_add_deleted_at_to_product_colors_table', 1),
(45, '2024_09_17_064392_add_deleted_at_to_product_images_table', 1),
(46, '2024_09_17_064393_add_deleted_at_to_product_sizes_table', 1),
(47, '2024_09_17_064394_add_deleted_at_to_product_units_table', 1),
(48, '2024_09_17_064395_add_deleted_at_to_products_table', 1),
(49, '2024_09_17_064396_add_deleted_at_to_units_table', 1),
(50, '2024_09_17_064398_update_product_image_table', 1),
(51, '2024_09_17_064399_add_is_freeze_to_stores_table', 1),
(52, '2024_09_17_064400_add_soft_deletes_to_lines_table', 1),
(53, '2024_09_17_064401_add_soft_deletes_to_divisions_table', 1),
(54, '2024_09_17_064402_add_soft_deletes_to_shelves_table', 1),
(55, '2024_09_18_064403_add_code_column_to_shelves_table', 1),
(56, '2024_09_18_064404_create_store_categories_table', 1),
(57, '2024_09_18_064405_create_product_limit_table', 1),
(58, '2024_09_18_064406_create_settings_table', 1),
(59, '2024_09_22_064750_create_recipes_table', 2),
(60, '2024_09_22_064817_create_ingredients_table', 2),
(61, '2024_09_22_071201_create_recipe_images_table', 2),
(62, '2024_09_22_080541_create_recipes_table', 3),
(63, '2024_09_22_080549_create_ingredients_table', 3),
(64, '2024_09_22_095559_create_recipes_table', 4),
(65, '2024_09_22_095607_create_recipe_images_table', 4),
(66, '2024_09_22_095611_create_ingredients_table', 4),
(67, '2024_09_22_120756_create_floors_table', 5),
(68, '2024_09_22_120805_create_tables_table', 5),
(69, '2024_09_23_064237_create_discounts_table', 5),
(70, '2024_09_23_064255_create_coupons_table', 5),
(71, '2024_09_23_063531_add_price_and_tax_to_settings_table', 6),
(72, '2024_09_23_071528_add_branch_id_to_floors_table', 6),
(73, '2024_09_23_073249_create_addons_table', 6),
(74, '2024_09_23_073256_create_recipe_addons_table', 6),
(75, '2024_09_23_101943_create_client_addresses_table', 6),
(76, '2024_09_23_101944_create_client_details_table', 6),
(77, '2024_09_23_114625_add_product_and_details_to_recipe_addons_table', 6),
(78, '2024_09_23_115037_remove_price_from_addons_table', 6),
(79, '2024_09_24_120806_create_orders_table', 6),
(80, '2024_09_24_120807_create_order_details_table', 6),
(81, '2024_09_24_120808_create_order_trackings_table', 6),
(82, '2024_09_24_120809_create_order_transactions_table', 6),
(83, '2024_09_24_120810_create_order_refunds_table', 6),
(84, '2024_09_24_120811_add_ids_to_orders_table', 6),
(85, '2024_09_24_120812_add_ids_to_order_transactions_table', 6),
(86, '2024_09_24_120813_update_ids_to_order_detailss_table', 6),
(87, '2024_09_23_122653_create_recipe_categories_table', 7),
(88, '2024_09_23_123022_add_category_id_to_recipes_table', 7),
(89, '2024_09_23_111302_alter_table_store_transactions_change_store_id', 8),
(90, '2024_09_23_130043_add_client_details_id_to_client_addresses_table', 8),
(91, '2024_09_24_120814_update_order_details_table', 8),
(92, '2024_09_24_120815_create_order_addons_table', 8),
(93, '2024_09_24_065903_drop_password_column_from_client_details_table', 9),
(94, '2024_09_24_120816_update_order_date_table', 9),
(95, '2024_09_24_120817_update_order_date_table', 9),
(96, '2024_09_24_120817_update_order_refund_table', 10),
(97, '2024_09_24_120818_update_orders_inv_num_table', 10),
(98, '2024_09_24_095557_add_expired_date_to_opening_balance_table', 11),
(101, '2024_09_24_120819_update_order_refunds_inv_num_table', 11),
(102, '2024_09_24_120820_update_product_is_have_expired_table', 11),
(103, '2024_09_24_120821_update_coupon_count_usage_table', 11),
(104, '2024_09_24_120822_update_order_transaction_discount_table', 11),
(105, '2024_09_24_120823_update_product_limit_store_id_table', 11),
(106, '2024_09_25_085137_add_discount_application_to_settings_table', 12),
(107, '2024_09_25_092417_remove_used_times_from_coupons_table', 12),
(108, '2024_09_24_120824_update_order_branch_id_table', 13),
(109, '2024_09_25_090019_add_service_fees_to_settings_table', 13),
(110, '2024_09_25_100156_add_is_kitchen_to_stores_table', 14),
(111, '2024_09_25_094006_modify_tax_column_in_settings_table', 15),
(112, '2024_09_25_094007_update_order_data_table', 16),
(113, '2024_09_25_094008_update_order_price_table', 16),
(114, '2024_09_25_094009_update_order_addons_price_table', 16),
(115, '2024_09_25_130709_create_product_transaction_logs_table', 16),
(116, '2024_09_26_091916_add_meal_type_to_recipes_table', 16),
(118, '2024_09_26_093444_drop_discount_application_from_settings_table', 17),
(119, '2024_09_26_093636_add_coupon_application_to_settings_table', 17),
(120, '2024_09_26_095731_add_has_kids_area_to_branches_table', 17),
(121, '2024_09_26_121306_create_cuisines_table', 18),
(122, '2024_09_26_131606_add_image_path_to_addons_table', 18),
(123, '2024_09_29_051906_add_cuisine_id_to_recipes_table', 19),
(124, '2024_09_26_094002_add_column_type_product_transaction_log_table', 20),
(125, '2024_09_26_105221_add_column_order_details_id_product_transaction_log', 20),
(126, '2024_09_26_112424_add_column_order_addon_id_product_transaction_log', 20),
(127, '2024_09_26_143517_add_column_invoice_num_store_transactions', 20),
(128, '2024_09_29_071158_remove_price_from_recipe_addons_table', 20),
(129, '2024_09_29_071225_add_price_to_addons_table', 20),
(130, '2024_09_30_064420_create_purchase_invoices_table', 21),
(131, '2024_09_30_065518_create_purchase_invoices_details_table', 21),
(132, '2024_09_30_084734_add_purchase_invoice_id_to_store_transaction_table', 21),
(133, '2024_10_02_071033_add_price_column_to_products_table', 21),
(134, '2024_10_02_071200_add_expiry_date_to_products_table', 21),
(135, '2024_10_15_061246_create_dishes_table', 21),
(136, '2024_10_15_063406_create_dish_details_table', 22),
(137, '2024_10_15_091733_remove_recipe_id_from_dishes_table', 22),
(138, '2024_10_15_093423_add_loss_percent_to_ingredients_table', 23),
(139, '2024_10_15_122036_update_recipe_table_for_type_column', 24),
(140, '2024_10_14_114733_delete_purchase_invoice_id_in_store_transaction', 25),
(141, '2024_10_14_115119_add_invoice_id_in_store_transaction', 25),
(142, '2024_10_14_135420_delete_foreignkey_order_id_from_product_transaction_logs', 25),
(143, '2024_10_14_141052_add_order_id_from_product_transaction_logs', 25),
(148, '2024_10_16_054212_remove_addon_id_from_recipe_addons_table', 26),
(149, '2024_10_16_054307_drop_addons_table', 27),
(150, '2024_10_16_080047_create_brands_table', 28),
(151, '2024_10_16_054905_update_table_replace_recipe_with_dish', 29),
(155, '2024_10_16_084134_remove_product_columns_from_recipe_addons_table', 31),
(156, '2024_10_16_084917_remove_quantity_from_recipe_addons_table', 32),
(157, '2024_10_16_085716_add_addon_id_to_recipe_addons_table', 33),
(158, '2024_10_16_090631_rename_recipe_categories_to_dish_categories', 34),
(159, '2024_10_16_091250_update_foreign_keys_for_dish_categories', 35),
(160, '2024_10_17_063037_create_branch_recipe_table', 36),
(161, '2024_10_17_063517_create_branch_dish_table', 37),
(163, '2024_10_16_105253_drop_product_id_and_unit_id_from_order_details_table', 38),
(164, '2024_10_16_111310_create_order_product_table', 38),
(166, '2024_10_16_074659_add_brand_id_to_products_table', 39),
(167, '2024_09_24_120815_create_point_system_table', 40),
(168, '2024_09_24_120815_create_point_transactions_table', 40),
(169, '2024_09_25_094006_update_columns_point_system_table', 40),
(170, '2024_10_15_113802_drop_point_systems_and_point_transactions_tables', 40),
(171, '2024_10_15_114652_add_new_point_systems_table', 40),
(172, '2024_10_15_115059_add_new_point_products_table', 40),
(173, '2024_10_15_121931_add_new_point_transactions_table', 40),
(174, '2024_10_16_075707_modify_point_systems_table', 40),
(175, '2024_10_16_094900_modify_new_column_point_systems_table', 40),
(176, '2024_10_16_124228_modify_point_redeem_column_point_systems_table', 40),
(177, '2024_10_17_074532_add_points_num_to_order_transactions_table', 41),
(179, '2024_10_16_075700_modify_flag_column_in_users_table', 42),
(180, '2024_10_20_055730_update_order_refunds_table', 43),
(181, '2024_10_20_091621_create_gifts_table', 43),
(182, '2024_10_21_070129_create_branch_coupon_table', 43),
(183, '2024_10_21_084107_create_user_gift_table', 44),
(184, '2024_10_21_121405_create_branch_discount_table', 44),
(185, '2024_10_21_121412_create_dish_discount_table', 44),
(186, '2024_10_21_125937_add_description_columns_to_dishes_table', 44),
(187, '2024_10_21_131309_fix_category_relation_in_dishes_table', 44),
(188, '2024_10_21_132001_update_dishes_category_relation', 44),
(189, '2024_10_21_144139_delete_order_addon_id_from_product_transaction_logs', 44),
(190, '2024_10_22_070547_rename_recipe_addons_to_dish_addons', 44),
(191, '2024_10_22_090205_deleted_order_addon_id_from_product_transaction_logs', 44),
(192, '2024_10_22_105246_deleted_recipe_addon_id_from_order_addons', 44),
(193, '2024_10_22_105844_deleted_unit_id_from_order_products', 44),
(194, '2024_10_23_125725_create_employees_table', 44),
(195, '2024_10_24_061301_create_departments_table', 44),
(196, '2024_10_24_061319_create_positions_table', 44),
(197, '2024_10_24_065702_add_store_use_to_settings_table', 44),
(198, '2024_10_24_092814_edit_order_type_on_product_transaction_logs', 44),
(199, '2024_10_24_114650_add_withdrawal_store_on_setting', 44),
(200, '2024_10_24_124637_add_order_type_on_product_transaction_logs', 44),
(201, '2024_10_27_081809_add_job_years_in_countries', 44),
(202, '2024_10_27_082028_create_leave_types_table', 44),
(203, '2024_10_27_082102_create_leave_settings_table', 44),
(204, '2024_10_27_082140_create_leave_nationals_table', 44),
(205, '2024_10_27_082226_create_overtime_types_table', 44),
(206, '2024_10_27_082234_create_overtime_settings_table', 44),
(207, '2024_10_27_092552_create_excuse_requests_table', 44),
(208, '2024_10_27_100405_create_excuses_table', 44),
(209, '2024_10_27_132303_create_leave_requests_table', 44),
(210, '2024_10_27_135909_create_penalty_reasons_table', 44),
(211, '2024_10_28_065224_add_agreement_column_in_leave_requests', 44),
(212, '2024_10_28_092014_add_reservation_time_in_settings', 44),
(213, '2024_10_28_092348_create_floor_partitions_table', 44),
(214, '2024_10_28_092542_add_floor_partition_id_in_tables', 44),
(215, '2024_10_28_101212_create_excuse_settings_table', 44),
(216, '2024_10_28_105134_create_table_reservations_table', 44),
(217, '2024_10_28_112351_create_penalties_table', 44),
(218, '2024_10_28_125133_add_excuse_hours_to_employees_table', 44),
(219, '2024_10_28_130138_create_delay_times_table', 44),
(220, '2024_10_28_130143_create_delays_table', 44),
(221, '2024_10_29_082632_add_translation_columns_to_discounts_table', 44),
(222, '2024_10_29_110354_update_branches_table_replace_opening_hours', 44),
(223, '2024_10_29_110401_add_hot_line_column_in_settings', 44),
(224, '2024_10_29_120710_add_name_column_in_client_addresses', 44),
(225, '2024_10_29_120906_create_delivery_settings_table', 44),
(226, '2024_10_29_132913_delete_created_by_column_in_store_transactions', 44),
(227, '2024_10_29_132925_add_created_by_column_in_store_transactions', 44),
(228, '2024_10_29_133502_delete_created_by_column_in_product_transactions', 44),
(229, '2024_10_29_133510_add_created_by_column_in_product_transactions', 44),
(230, '2024_10_29_133541_delete_created_by_column_in_product_transaction_logs', 44),
(231, '2024_10_29_133550_add_created_by_column_in_product_transaction_logs', 44),
(232, '2024_10_29_133655_delete_created_by_column_in_tables', 44),
(233, '2024_10_29_133702_add_created_by_column_in_tables', 44),
(234, '2024_10_29_133732_delete_created_by_column_in_table_reservations', 44),
(235, '2024_10_29_133746_add_created_by_column_in_table_reservations', 44),
(236, '2024_10_29_133924_delete_created_by_column_in_floors', 44),
(237, '2024_10_29_133931_add_created_by_column_in_floors', 44),
(238, '2024_10_29_134050_delete_created_by_column_in_floor_partitions', 44),
(239, '2024_10_29_134100_add_created_by_column_in_floor_partitions', 44),
(240, '2024_10_30_093944_add_quantity_to_dish_addons_table', 44),
(241, '2024_10_30_095303_add_price_to_branch_dish_table', 44),
(242, '2024_10_30_101134_rename_branch_dish_to_menu', 44),
(243, '2024_10_30_121717_add_soft_deletes_and_deleted_by_to_menu_table', 44),
(244, '2024_10_31_102607_create_advance_settings_table', 44),
(245, '2024_10_16_060047_create_brands_table', 1),
(246, '2024_11_03_081213_create_advance_requests_table', 45),
(247, '2024_11_03_081229_create_advances_table', 45),
(248, '2024_11_03_121534_make_password_nullable_in_users_table', 46),
(249, '2024_11_03_121605_add_google_id_to_users_table', 46),
(250, '2024_11_04_100919_add_facebook_id_to_users_table', 47),
(251, '2024_11_04_111452_add_facebook_id_to_users_table', 48),
(252, '2024_11_03_102402_create_timetables_table', 49),
(253, '2024_11_03_122710_create_shifts_table', 49),
(254, '2024_11_03_122746_create_shift_details_table', 49),
(255, '2024_11_03_123724_create_employee_schedules_table', 49),
(256, '2024_11_04_092720_create_employee_floor_partitions_table', 49),
(257, '2024_11_04_102025_create_cashier_machines_table', 49),
(258, '2024_11_04_102028_create_employee_opening_balances_table', 49),
(271, '2024_10_31_101220_create_einvoices_table', 51),
(272, '2024_11_04_074310_create_einvoice_settings_table', 51),
(273, '2024_11_05_080400_update_client_addresses_table', 51),
(274, '2024_11_05_085718_update_order_type_enum_in_orders_table', 51),
(275, '2024_11_05_091631_update_order_status_enum_in_orders_table', 51),
(276, '2024_11_05_091644_add_cross_day_to_timetables_table', 51),
(277, '2024_11_05_093816_add_time_column_to_orders_table', 51),
(278, '2024_11_05_131008_add_order_type_to_order_transactions_table', 51),
(279, '2024_11_05_104452_create_payrolls_table', 52),
(280, '2024_11_06_065454_add_employee_id_to_penalty_deductions_table', 52),
(281, '2024_11_06_065500_add_employee_id_to_delay_deductions_table', 52),
(282, '2024_11_06_071521_create_delay_deductions_table', 53),
(283, '2024_11_06_071606_create_penalty_deductions_table', 53),
(284, '2024_11_06_110636_create_offers_table', 54),
(285, '2024_11_06_121105_create_offer_details_table', 54),
(286, '2024_11_06_100224_add_time_cancellation_to_settings_table', 55),
(287, '2024_11_06_115241_add_closing_cashier_column_on_setting', 55),
(288, '2024_11_06_121030_create_cashier_machine_logs_table', 55),
(289, '2024_11_07_084358_update_offer_type_enum_in_offer_details_table', 55),
(290, '2024_11_10_074626_add_transgression_leave_column_on_setting', 56),
(291, '2024_11_11_164621_add_deleted_at_to_employees_table', 57),
(292, '2024_11_14_141851_drop_email_and_phone_columns_from_client_details_table', 58),
(293, '2019_09_15_000010_create_tenants_table', 59),
(294, '2019_09_15_000020_create_domains_table', 59),
(295, '2024_12_04_113942_add_cascade_delete_to_client_addresses', 60),
(296, '2024_12_09_091738_remove_expiry_date_and_price_from_products_table', 61),
(297, '2024_12_09_114218_alter_flag_column_in_users_table', 62),
(298, '2024_12_09_120051_create_nationalities_table', 62),
(299, '2024_12_09_122215_alter_nationality_column_in_employees_table', 62),
(300, '2024_12_10_074122_make_store_id_nullable_in_product_limit_table', 63),
(301, '2024_11_17_081024_create_dish_sizes_table', 64),
(302, '2024_11_17_082100_add_dish_size_id_to_menu_table', 64),
(303, '2024_11_17_082732_add_has_sizes_to_dishes_table', 64),
(304, '2024_11_17_083206_add_dish_size_id_to_dish_details_table', 64),
(305, '2024_12_09_093812_add_employee_id_to_branches', 64),
(306, '2024_12_09_110450_create_logos_table', 64),
(307, '2024_12_10_085328_create_sliders_table', 64),
(308, '2024_12_10_074416_create_permission_tables', 65),
(309, '2024_12_12_085005_remove_unique_constraint_from_positions_name', 65),
(310, '2024_12_12_085306_add_transaction_type_to_order_transactions', 65),
(311, '2024_12_12_085307_drop_first_name_client_details', 65),
(312, '2024_12_12_085308_drop_name_en_client_address', 65),
(313, '2024_12_12_100043_add_client_address_id_to_orders_table', 65),
(314, '2024_12_12_102229_change_dish_addon_id_to_not_null_in_order_addons', 65),
(315, '2024_12_12_105510_create_addon_categories_table', 65),
(316, '2024_12_12_113641_add_category_to_menus', 65),
(317, '2024_12_12_121411_create_branch_menu_categories_table', 65),
(318, '2024_12_12_122946_add_time_to_order_trackings', 65),
(319, '2024_12_12_124330_add_menu_category_to_menus', 65),
(320, '2024_12_12_124921_add_actiev_to_menus', 65),
(321, '2024_12_12_140643_remove_price_from_recipes_table', 65),
(322, '2024_12_12_141815_add_price_and_addon_category_id_to_dish_addons_table', 65),
(323, '2024_12_12_141945_create_branch_menu_addons_table', 65),
(324, '2024_12_15_081855_create_terms_and_conditions_table', 65),
(325, '2024_12_15_081949_create_privacy_policies_table', 65),
(326, '2024_12_15_082023_create_return_policies_table', 65),
(327, '2024_12_15_082331_add_inprogress_to_status_enum_in_orders_table', 65),
(328, '2024_12_15_113556_add_image_column_to_dishes_table', 65),
(329, '2024_12_16_082120_create_f_a_q_s_table', 65),
(330, '2024_12_16_071530_remove_price_from_recipes_table', 66),
(331, '2024_12_16_082225_add_default_size_to_dish_sizes_table', 66),
(332, '2024_12_16_111801_add_min_max_addons_to_dish_addons_table', 66),
(333, '2024_12_16_115735_add_uuid_to_leave_type', 66),
(334, '2024_12_16_125249_add_uuid_to_country', 66),
(335, '2024_12_16_130111_update_dishes_table_created_by_nullable', 66),
(336, '2024_12_16_132416_add_uuid_to_leave_setting', 66),
(337, '2024_12_16_140826_add_has_addon_to_dishes_table', 66),
(338, '2024_12_17_090023_add_column_active_and_change_to_uuid_terms_and_conditions', 66),
(339, '2024_12_17_090051_add_column_active_and_change_to_uuid_privacy_policies', 66),
(340, '2024_12_17_090113_add_column_active_and_change_to_uuid_return_policies', 66),
(341, '2024_12_17_090137_add_column_active_and_change_to_uuid_f_a_q_s', 66),
(342, '2024_12_17_090303_change_to_uuid_logos', 66),
(343, '2024_12_17_090334_change_to_uuid_sliders', 66),
(344, '2024_12_17_135004_add_max_min_in_branch_menu_addons', 66),
(345, '2024_12_17_141824_add_cuisine_id_in_menu', 66),
(346, '2024_12_18_090843_add_columns_phone_code_and_phone_length_countries_table', 67),
(347, '2024_12_18_095630_add_column_flag_countries_table', 68),
(348, '2024_12_18_072832_add_status_column_to_order_addons_table', 69),
(349, '2024_12_18_072857_update_status_column_in_order_details_table', 69),
(350, '2024_12_18_100228_create_otps_table', 69),
(351, '2024_12_18_113345_add_country_code_column_to_users_table', 69),
(352, '2024_12_18_094454_remive_dish_size_in_menu', 70),
(353, '2024_12_18_100028_create_branch_menu_sizes_table', 70),
(354, '2024_12_18_111234_edit_cuisine_id_nullable_in_menu', 70),
(355, '2024_12_18_141448_make_country_id_nullable_in_users_table', 71),
(357, '2024_12_18_142544_add_columns_to_client_addresses_table', 72),
(358, '2024_12_19_111521_drop_menu_foreign_key_on_menu', 72),
(359, '2024_12_19_114831_create_branch_menus_table', 72),
(360, '2024_12_19_121348_add_uuid_country_to_user', 72),
(361, '2024_12_19_163312_add_service_fees_to_orders_table', 72),
(362, '2024_12_19_163340_add_total_to_order_addons_table', 72),
(363, '2024_12_22_085308_add_fields_to_dish_discount_table', 73),
(364, '2024_12_22_110022_add_columns_discount_type_discount_value_branch_id_to_offers', 74),
(365, '2024_12_22_110226_remove_columns_discount_offer_type_addons_products_from_offer_details', 74),
(366, '2024_12_22_113635_change_branch_menu_id_on_branch_menu_sizes', 74),
(367, '2024_12_22_113919_create_branch_menu_addon_categories_table', 74),
(368, '2024_12_22_114138_change_branch_menu_id_on_branch_menu_addons', 74),
(369, '2024_12_22_114759_drop_dish_category_id_on_branch_menus', 74),
(370, '2024_12_22_115858_add_offer_id_to_order_details_table', 74),
(371, '2024_12_22_124556_drop_parent_id_on_branch_menu_categories', 74),
(372, '2024_12_22_151808_add_is_default_on_branches', 74),
(373, '2024_12_23_101842_remove_unique_constraint_from_code_size_in_product_sizes', 75),
(374, '2024_12_23_115017_create_rates_table', 76),
(375, '2024_12_23_121244_create_user_favorite_dishes_table', 76),
(376, '2024_12_24_072407_remove_unique_constraint_from_code_in_coupon', 77),
(377, '2024_12_24_092834_add_column_active_to_rates', 78),
(378, '2024_12_24_115343_add_currency_symbol_to_countries_table', 79),
(379, '2024_12_26_072740_add_is_active_to_permissions_table', 80),
(380, '2024_12_26_094352_make_country_code_nullable_in_users_table', 81),
(381, '2024_12_26_131411_change_column_currency_code_in_products', 82),
(382, '2024_12_29_082206_create_jobs_table', 83),
(383, '2024_12_29_070300_add_foreign_branch_id_to_employees_table', 84),
(384, '2024_12_29_131810_add_column_discount_id_to_sliders', 85),
(385, '2024_12_30_071636_update_flag_column_in_sliders', 85),
(386, '2024_12_30_133224_drop_country_column_from_client_addresses_table', 86),
(387, '2025_01_01_081141_add_column_delivery_time_setings_table', 87),
(388, '2025_01_01_095050_change_time_column_in_orders_table', 88),
(389, '2025_01_01_095506_add_dish_size_column_to_order_details_table', 88),
(390, '2025_01_02_152042_create_branch_times_table', 89),
(391, '2025_01_05_095109_edit_day_column_in_branch_time', 89),
(392, '2025_01_06_141711_drop_date_of_birth_from_client_details_table', 90),
(393, '2025_01_07_082447_add_transaction_column_in_table_order_transaction', 91),
(394, '2025_01_08_110512_create_order_settings_table', 91),
(395, '2025_01_09_120740_update_employee_foreign_key_on_advance_requests', 91),
(396, '2025_01_09_121157_update_employee_foreign_key_on_advances_table', 91),
(397, '2025_01_09_121158_drop_supervisor_id_in_employees', 91),
(398, '2025_01_09_121702_update_supervisor_id_foreign_key_on_employees', 91),
(399, '2025_01_09_124840_update_employee_id_foreign_key_on_branches', 91),
(400, '2025_01_13_124539_remove_unique_from_barcode_and_sku_in_products_table', 92),
(401, '2025_01_14_093913_add_country_code_to_employees', 93),
(402, '2025_01_14_114556_add_is_active_column_to_users', 94),
(403, '2025_01_14_114826_drop_client_details_table', 94),
(404, '2025_01_19_121230_add_columns_to_branches_table', 95),
(405, '2025_01_19_150329_update_time_fields_to_integer_in_branches_table', 96),
(406, '2025_01_19_164503_add_passwrodon_employee_table', 97),
(407, '2025_01_20_103001_add_order_details_id_to_order_addon_table', 97),
(408, '2025_01_20_110535_create_kitchen_logs_table', 98),
(409, '2025_01_20_135120_rename_services_fees_to_service_fees_in_branches_table', 98),
(410, '2025_01_20_140802_edit_status_in_order_details', 98),
(411, '2025_01_20_142636_edit_status_in_order_addons', 98),
(412, '2025_01_20_142936_add_active_in_branches', 98),
(413, '2025_01_20_145022_add_column_to_branches_table', 99),
(414, '2025_01_20_150045_edit_active_in_branches', 99),
(415, '2025_01_20_154224_update_user_favorite_dishes_table', 99),
(416, '2025_01_21_124449_edit_order_type_in_kitchen_log', 100),
(417, '2025_01_22_094307_add_tax_value_to_order_addons_table', 100),
(418, '2025_01_22_094704_drop_total_and_price_from_order_addon_table', 100),
(419, '2025_01_22_094705_drop_fees_from_orders_table', 100),
(420, '2025_01_22_100757_drop_order_settings_table', 100),
(422, '2025_01_23_131406_add_model_type_to_role_user_table', 102),
(423, '2025_01_21_132235_update_flag_column_in_employees_table', 103),
(424, '2025_01_23_141244_create_user_coupons_table', 104),
(425, '2025_01_26_121717_add_payment_failed_status_to_order_transactions_table', 105),
(426, '2025_01_27_121814_update_and_add_columns_cilent_address_table', 106),
(427, '2025_01_27_150144_add_soft_deletes_to_users_table', 107),
(428, '2025_01_28_101921_update_status_column_in_einvoices', 108),
(429, '2025_01_28_105232_make_floor_number_nullable_in_client_addresses_table', 108),
(430, '2025_01_28_140724_add_reset_to_branch', 109),
(431, '2025_01_29_111833_add_code_to_dishes_table', 109),
(432, '2025_01_29_123753_add_code_to_recipes_table', 110),
(433, '2025_01_29_135549_create_payment_methods_table', 111),
(434, '2025_01_29_143239_branch_poses_table', 111);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AppModelsUser',
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 9),
(3, 'App\\Models\\User', 41),
(3, 'App\\Models\\User', 67),
(3, 'AppModelsUser', 71),
(4, 'AppModelsUser', 72),
(4, 'AppModelsUser', 74);

-- --------------------------------------------------------

--
-- Table structure for table `nationalities`
--

CREATE TABLE `nationalities` (
  `id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nationalities`
--

INSERT INTO `nationalities` (`id`, `name_ar`, `name_en`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'مصري', 'Egyptian', NULL, NULL, NULL, NULL),
(2, 'سعودي', 'Saudi', NULL, NULL, NULL, NULL),
(3, 'سوداني', 'Sudanese', NULL, NULL, NULL, NULL),
(4, 'أردني', 'Jordanian', NULL, NULL, NULL, NULL),
(5, 'عراقي', 'Iraqi', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `date_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `title_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_access_tokens`
--

INSERT INTO `oauth_access_tokens` (`id`, `user_id`, `client_id`, `name`, `scopes`, `revoked`, `created_at`, `updated_at`, `expires_at`) VALUES
('0063a59849d66f743f380d303b73ee0e57918e4bae59b9833878dcdb3fab8dc6ec7715707e0806d1', 33, 1, 'myToken', '[]', 0, '2024-12-31 12:33:43', '2024-12-31 12:33:43', '2025-01-01 13:33:43'),
('006a49397b563aff00bdb2f042e4ae2c0786f47b9f4306e5c0c1dad0ffe4e70efcda8017b3c622cc', 39, 1, 'myToken', '[]', 0, '2025-01-02 09:26:03', '2025-01-02 09:26:03', '2025-01-03 10:26:03'),
('0175cecec34a5aaaf8a1aac76946ea1612150582a345168073b41f3035e62a150ef1ad626ac2439c', 17, 1, 'myToken', '[]', 0, '2024-12-18 12:43:17', '2024-12-18 12:43:17', '2025-12-18 13:43:17'),
('02025d4ff10c19742ad90efccd35ad97d2385008093bf3031fcfa60a525a07c85436709d4ce71fd1', 37, 1, 'myToken', '[]', 0, '2025-01-19 11:57:33', '2025-01-19 11:57:33', '2025-01-20 12:57:33'),
('045a6c6741d8afbf63bcdee32f9f6ca6be205e285aa9a52da7a0bcbe7cb090af3e9f780b81ec1225', 33, 1, 'myToken', '[]', 0, '2025-01-02 12:53:03', '2025-01-02 12:53:04', '2025-01-03 13:53:04'),
('0590447a0be6f9a27cb9ade26ada87d0a7491f385b4aa70955a6204f36685890d64f55849e254288', 26, 1, 'MyApp', '[]', 0, '2024-12-19 10:11:17', '2024-12-19 10:11:17', '2025-12-19 11:11:17'),
('068814e41fd9b8fed4da49fdfd69fced8881f288e6252c35cb08e68bf6c3de5ebcb46137ff396e24', 10, 1, 'myToken', '[]', 0, '2024-11-20 07:49:14', '2024-11-20 07:49:14', '2025-11-20 09:49:14'),
('069ed0bc38b43c98048c43abb2a19aa6c6772f9fe2463c0dea30dbd125411a9381d82bf41cf7d9b1', 15, 1, 'myToken', '[]', 0, '2024-12-19 05:30:15', '2024-12-19 05:30:15', '2025-12-19 06:30:15'),
('06bef79a597a95a3c7d08e6cd9affe0bf742e85e246915c82e8c47c8646a4a3f84cbf9394adea296', 33, 1, 'MyApp', '[]', 0, '2024-12-22 08:17:33', '2024-12-22 08:17:33', '2025-12-22 09:17:33'),
('075ffacacd80f028c7be8fbbbcef27f999e66647579efe42f57cebc0e31eb6f0551f45e176dc5925', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:11:26', '2024-12-19 12:11:26', '2025-12-19 13:11:26'),
('07a87578548e4f8aa1bc4f6839e31907cef79779871e13b7296e0a8c785fa0d4ff8043c569e8fc9e', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:12:54', '2024-12-19 12:12:54', '2025-12-19 13:12:54'),
('0832a33955069e1ef4f63321f6dbac007982a50026e920537e561440715fe1e004815294f04f79f6', 39, 1, 'myToken', '[]', 0, '2024-12-25 07:54:12', '2024-12-25 07:54:12', '2025-12-25 08:54:12'),
('0862aac6d169f6c9f5d6e312bc9c5a22d2767cd76636271be09c697bf2cfb5d56660f8621a02eeb0', 17, 1, 'myToken', '[]', 0, '2024-12-18 13:20:39', '2024-12-18 13:20:39', '2025-12-18 14:20:39'),
('089b145a77892dd0212b4f0e5a357cb659b0c163b3449cb03a5efec778ee45c47e90d43167934ae3', 15, 1, 'myToken', '[]', 0, '2024-12-18 07:50:31', '2024-12-18 07:50:31', '2025-12-18 08:50:31'),
('08cb0e6f73bc48d72ad5607abb64c4302b23fd7cb5dbe4ad9405079cb18ad816d963b99edbfcabde', 33, 1, 'myToken', '[]', 0, '2024-12-25 06:53:11', '2024-12-25 06:53:11', '2025-12-25 07:53:11'),
('09001b4d7870f56eed7047019ef98cf51e86de66ea108edba7f9bb94dcf892ec5d67555ec0a8e0ca', 15, 1, 'myToken', '[]', 0, '2024-12-19 10:32:52', '2024-12-19 10:32:52', '2025-12-19 11:32:52'),
('09883c0edc44a339ea8043e8d7b6bf4584aaa1061f7550dcebed21e45a99f9347df16b7852bbb2f8', 17, 1, 'myToken', '[]', 0, '2024-12-19 05:26:52', '2024-12-19 05:26:53', '2025-12-19 06:26:52'),
('0ba2bbe627e72cb89d7e582ef51739d98cc31bae7eeed44d83c363ae75bd1f099086d90a8117ff51', 33, 1, 'myToken', '[]', 0, '2024-12-31 08:17:22', '2024-12-31 08:17:22', '2025-01-01 09:17:22'),
('0bc56eef7fc816ef97d1800d404e19db3ded3e0091c53f816d031b9daf87a764f93834f8cf28e587', 37, 1, 'myToken', '[]', 0, '2025-01-19 11:57:57', '2025-01-19 11:57:57', '2025-01-20 12:57:57'),
('0d5117f50e6e143edc410735037f81af877381da7624214bf999ad9b531d003b611fe507f1646a8f', 16, 1, 'myToken', '[]', 0, '2024-12-18 08:27:52', '2024-12-18 08:27:52', '2025-12-18 09:27:52'),
('0d5e0b614624971a1d6d8dc8a54e71507bc80a769dbf3b97254b5366452f8abb7ec099367e02ea5b', 33, 1, 'myToken', '[]', 0, '2024-12-19 13:04:57', '2024-12-19 13:04:57', '2025-12-19 14:04:57'),
('0d88a3ab01bac669befd0a5dfaba952308187387e3bc8ae7f212c2bcf5bd8bf21c96cd299afb41c0', 39, 1, 'myToken', '[]', 0, '2025-01-20 12:43:46', '2025-01-20 12:43:46', '2025-01-21 13:43:46'),
('10025ddb02cfcb8d59e8e75bf84d24d9609f11c1d713e9d4c03760eff0d397bc1f59bb5d24222d9a', 15, 1, 'myToken', '[]', 0, '2024-12-22 09:20:50', '2024-12-22 09:20:50', '2025-12-22 10:20:50'),
('107b616e634aad2862846cc30272c0772a9a267c9dab9cbe9e3b346862723fceeb5451e66084919a', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:10:39', '2024-12-19 12:10:39', '2025-12-19 13:10:39'),
('113178f6945b16791bb3f88e3ec5169db02ed3cb9c3ee9bc789ee25a6e5973b5b4c7fe218df5927e', 39, 1, 'myToken', '[]', 0, '2025-01-27 16:08:04', '2025-01-27 16:08:05', '2025-01-28 17:08:05'),
('12a81639851b8cf023f56aad79eef4f0741d80d7b358bffd9a5a3ae5ccaede50c176d10f52618ce7', 10, 1, 'myToken', '[]', 0, '2024-11-18 05:44:28', '2024-11-18 05:44:28', '2025-11-18 07:44:28'),
('155783ff0adba5006af3c226e6a8bf076aeb12637d6ce6d44fcc3980b82c594b5fa587c047869f48', 33, 1, 'myToken', '[]', 0, '2024-12-29 10:50:59', '2024-12-29 10:50:59', '2024-12-30 11:50:59'),
('1629b7134e9b4374df4477aa267c3200c7945c892e435b508be6ed4050a97c0140dc41c6101d5fa2', 37, 1, 'myToken', '[]', 0, '2025-01-15 14:27:54', '2025-01-15 14:27:55', '2025-01-16 15:27:55'),
('177ae68c8eda08aae6f46775ecfbf647ae181ad3e2d06732e515d5b22a7329ae0dc4911ae06dcba3', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:57:17', '2024-12-19 12:57:18', '2025-12-19 13:57:17'),
('188b449eda6de02031d5aeacfe8e483a042d85d0dcf7a9f35297bd79a8185f138ccf5950edd1c9c1', 18, 1, 'myToken', '[]', 0, '2024-12-18 08:44:48', '2024-12-18 08:44:48', '2025-12-18 09:44:48'),
('188f4fbfa9a3c80772869a6dc85e278aba66e01c5e95f872034e3f400449b6853cea4ab18a3f8b2e', 17, 1, 'myToken', '[]', 0, '2024-12-18 11:12:40', '2024-12-18 11:12:40', '2025-12-18 12:12:40'),
('18e7b18749777095e4fc6120bf77dac859a572ed7bf88e066792fec839744bc5584e06f583924ca1', 24, 1, 'myToken', '[]', 0, '2025-01-23 11:22:44', '2025-01-23 11:22:44', '2025-01-24 12:22:44'),
('18f6e6f5b1672afed6e3c50c281f0575ad83a453c597ffc45cb9e0b1cd586d01dd4be092b5f1b7d3', 33, 1, 'myToken', '[]', 0, '2024-12-22 12:33:55', '2024-12-22 12:33:56', '2025-12-22 13:33:55'),
('195826e3a7b5731c8de84752ccae4e1b61312e2b2e09a594c5b6eaca34c3e706023fa2b4faaf259b', 33, 1, 'MyApp', '[]', 0, '2024-12-22 09:24:53', '2024-12-22 09:24:53', '2025-12-22 10:24:53'),
('1a592feef7138ad38e8f390c881c2374e4447e9b23c0bc44f04b176dbaa6bd4ff61d42ee1c2731e1', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:14:48', '2024-12-19 12:14:48', '2025-12-19 13:14:48'),
('1f3720517bbedf03a305934362c7fd0b1670487803c5deeff3a8040c8424f2a42ef052cd67c2c4e1', 33, 1, 'myToken', '[]', 0, '2025-01-29 10:26:45', '2025-01-29 10:26:45', '2025-01-30 11:26:45'),
('1fa37fa7afce9d87e45af16cd7c39a75206f2803b1913542dbfde546d6f864f310e826f8f9033dd1', 17, 1, 'myToken', '[]', 0, '2024-12-19 13:08:23', '2024-12-19 13:08:23', '2025-12-19 14:08:23'),
('206e39eda84a1e138e0d3ce9999aaa83c9c48fad43753285ece0d9255d32ca2a08a1f3a59d162b03', 39, 1, 'myToken', '[]', 0, '2025-01-21 14:51:42', '2025-01-21 14:51:42', '2025-01-22 15:51:42'),
('21c4dd3367869a663f4a67bf863710be5f353c5de2f6d773a30836ef5e5c955c44c03ce2e8661c1d', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:10:38', '2024-12-19 12:10:39', '2025-12-19 13:10:38'),
('22bfadbc91383b8ebbbf40f545420f4e50250f10166333f90a4975a5f16afc71661b10410ed38774', 10, 1, 'myToken', '[]', 0, '2024-10-31 09:29:47', '2024-10-31 09:29:48', '2025-10-31 12:29:47'),
('22f958832e83297290f3064ea7a7a76ab2f5bead8974085c0bbcefe80cdfdfbb3fb00a4ab1a30285', 33, 1, 'myToken', '[]', 0, '2025-01-01 09:59:31', '2025-01-01 09:59:32', '2025-01-02 10:59:32'),
('23dc634c9f1535c8a33399120755b8085c24be28727c08cf2a85ed3df952f59f5f5f0896cce85559', 15, 1, 'myToken', '[]', 0, '2024-12-15 09:04:05', '2024-12-15 09:04:05', '2025-12-15 10:04:05'),
('268191b0178d53a8e6f9a3b187c73b5608f3cdebcb555cca6539cf420cf300f2750e1ae9e796d300', 69, 1, 'myToken', '[]', 0, '2025-01-20 14:26:14', '2025-01-20 14:26:14', '2025-01-21 15:26:14'),
('270b192931db26ac59912b2837c26dc0486cb796f0a678cedad68b93118ba9984d72eac32d7f3e60', 39, 1, 'myToken', '[]', 0, '2025-01-29 11:24:54', '2025-01-29 11:24:54', '2025-01-30 12:24:54'),
('272c9349300ce074a2c6346f9c94b45028151590f09491df3ac23d5e3d72516037768cdf707ebc16', 33, 1, 'myToken', '[]', 0, '2024-12-24 10:58:26', '2024-12-24 10:58:26', '2025-12-24 11:58:26'),
('2742d7c19b12e4bead975ece6e7022c6e2da7cd375b2645632721d3c366fa5874e7ba47684d8aa4f', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:57:37', '2024-12-19 12:57:37', '2025-12-19 13:57:37'),
('27f13aeba8fef59b124a63dd5aa8c24d0d9742c284b3afed3b73ef81ecee81da5d977b21f0113dcc', 33, 1, 'myToken', '[]', 0, '2025-01-28 07:56:00', '2025-01-28 07:56:00', '2025-01-29 08:56:00'),
('2801e0ded1cbf9643205a077562e298c8afe9db5c4334eea282920c009fa0466451232e61cc0ce77', 33, 1, 'myToken', '[]', 0, '2025-01-22 13:27:47', '2025-01-22 13:27:47', '2025-01-23 14:27:47'),
('28d51745cb5d753e359ab2bd2b38ca862713c7244575f7e1a7ab5cc4f348083358e5bc184487b466', 15, 1, 'myToken', '[]', 1, '2024-12-19 10:01:53', '2024-12-19 10:04:41', '2025-12-19 11:01:53'),
('2b45c48eea76be40bc1c4e2ab5eac19fe318ad311ca46688aa509a299f9462e4bbcf0ed1011ca597', 39, 1, 'myToken', '[]', 0, '2025-01-05 06:20:02', '2025-01-05 06:20:02', '2025-01-06 07:20:02'),
('2ed51eb450a778eacbc633039dfef3d85345a37f1598409bea33d48fe3ced4e5aa3c61371a308774', 15, 1, 'myToken', '[]', 0, '2024-12-18 11:54:22', '2024-12-18 11:54:22', '2025-12-18 12:54:22'),
('2f95740b5c351faa250e753734b83e244e2da5440ad9f7de77f6ce5dbbea6c3de77d1be68a9bf909', 37, 1, 'myToken', '[]', 0, '2025-01-20 09:51:14', '2025-01-20 09:51:15', '2025-01-21 10:51:15'),
('30467e918041ca2f3226e4729ee9dc2fcf738e7c72358dffafc42dcc76bb15f7f41c12763f5e36d5', 33, 1, 'myToken', '[]', 0, '2025-01-06 09:45:21', '2025-01-06 09:45:21', '2025-01-07 10:45:21'),
('3496fd54d3098fe97c9f46777d74ce75cd988df9f9404ffeaaa8a9bd34f9c4fc4ce578939cf183cf', 1, 1, 'myToken', '[]', 0, '2024-12-29 05:37:55', '2024-12-29 05:37:55', '2025-12-29 06:37:55'),
('34cbdbf9c16c7b242761b002d95640748e3b8e3199eb28ed0068c5f5c4ed90cc014b3e6f7aa79581', 33, 1, 'myToken', '[]', 0, '2024-12-22 09:16:54', '2024-12-22 09:16:54', '2025-12-22 10:16:54'),
('3587b86613b9ccd056a36955fe7fb0bb4463d2dba74b263eecc15d26698fde4669b740f35dcf2df1', 15, 1, 'myToken', '[]', 0, '2024-12-19 11:23:13', '2024-12-19 11:23:14', '2025-12-19 12:23:13'),
('36ecd29fb033eae826d08a5f5af5d41938ff12ec0909971bab7a8318c2a3f35071d41523ed97d402', 33, 1, 'myToken', '[]', 0, '2024-12-24 09:42:07', '2024-12-24 09:42:07', '2025-12-24 10:42:07'),
('38cc46361bea0fc676146f3c198e863449fbb93fca1ee541af9daacadbeee57ae3ae515ab30c7891', 15, 1, 'myToken', '[]', 0, '2024-12-19 11:31:21', '2024-12-19 11:31:21', '2025-12-19 12:31:21'),
('3bee2623488dc9786cfecd18ea2c7152471449371ef003bb020a05aab7174438945436cc2889600e', 27, 1, 'MyApp', '[]', 0, '2024-12-19 10:37:56', '2024-12-19 10:37:56', '2025-12-19 11:37:56'),
('3d9133d216bf20676e41bc2e8b56e742ad7e33c9965dfdcd2094a7c99b0a83e8e46fef51b960c84b', 39, 1, 'myToken', '[]', 0, '2025-01-02 12:55:13', '2025-01-02 12:55:13', '2025-01-03 13:55:13'),
('3e63480780fa4e828958a11966a4e90ccccfec19ad4d7b4fff3eb4e88f5e0abb06dde27d52a1a29c', 17, 1, 'myToken', '[]', 0, '2024-12-19 05:26:06', '2024-12-19 05:26:06', '2025-12-19 06:26:06'),
('3e870fca67e4fe10db6792a2010aeff1de3e9481677df455eb49599d76b17508a5f7939f29ebac09', 13, 1, 'GoogleAuthToken', '[]', 0, '2024-11-04 10:22:14', '2024-11-04 10:22:15', '2025-11-04 12:22:14'),
('3eed425f8c9fa2a2d4a6cd671fbd71d35da84f909e8b91cbd5419b4f02d1f31492f9e7122fe72ef1', 39, 1, 'myToken', '[]', 0, '2025-01-02 06:35:56', '2025-01-02 06:35:56', '2025-01-03 07:35:56'),
('3f0d2635470676d2271634165f22a5d43ae89924b5c2557ae76ba3349e482b08926f5e52fac3d8a9', 33, 1, 'myToken', '[]', 0, '2024-12-22 12:36:09', '2024-12-22 12:36:09', '2025-12-22 13:36:09'),
('3f3b7677ea03386118218b31d792f335a124a913436ff373c82210835b6b02fd97d145460522e75e', 60, 1, 'myToken', '[]', 0, '2025-01-07 08:06:16', '2025-01-07 08:06:16', '2025-01-08 09:06:16'),
('3f5561de69b96fdd375356981b4161723c813db94a016dfad5e358b03b6e02fa824c7333401a238c', 15, 1, 'myToken', '[]', 0, '2024-12-22 07:05:24', '2024-12-22 07:05:24', '2025-12-22 08:05:24'),
('3fe62161710b0275f4644577c3796166f9f266bdf28f9974468f55dd042a0675cde0a473b1255e34', 27, 1, 'MyApp', '[]', 0, '2024-12-19 10:38:46', '2024-12-19 10:38:46', '2025-12-19 11:38:46'),
('40d222e27aa683cdb406e4afeb40931cf087182d33390e11b45e66afa1f245e59ecd95e8a92a0b41', 27, 1, 'myToken', '[]', 0, '2025-01-01 07:31:25', '2025-01-01 07:31:26', '2025-01-02 08:31:26'),
('41a1d927fdd0a57c0df2af03c8aac8b7982a695c85453fecc8827e9a9971ff054367f12c02c53276', 24, 1, 'myToken', '[]', 0, '2024-12-19 07:09:23', '2024-12-19 07:09:23', '2025-12-19 08:09:23'),
('430ee20c630e221f72561cdaf676b3905416c9c4d072961c9f036b75b92967380a657af24219b50f', 33, 1, 'myToken', '[]', 0, '2025-01-28 09:55:22', '2025-01-28 09:55:23', '2025-01-29 10:55:23'),
('43491344a52d8feb63d9cf0fcf8a82e05b47aa117460e350999ccc63c19c7e7f45cb06361d38c488', 15, 1, 'myToken', '[]', 1, '2024-12-15 09:05:13', '2024-12-15 09:05:42', '2025-12-15 10:05:13'),
('43f2f6353a673e2be081ed495db482525ac353262ae5d010ce9b21d8f9c7c47574d794b8bc8d7f6e', 17, 1, 'myToken', '[]', 0, '2024-12-19 13:09:52', '2024-12-19 13:09:52', '2025-12-19 14:09:52'),
('4497a3f401c1aade716a3dc3c1a62ad9d52a751dc26e90d7a0f7248e6a0ef38e4517fbe5504cbb51', 33, 1, 'myToken', '[]', 0, '2024-12-31 06:18:09', '2024-12-31 06:18:09', '2025-01-01 07:18:09'),
('450b3f52559ffedfbf58121e987983ddfca3bda186a187c1ea4b67687d7fadca598bad1ff8fc339d', 33, 1, 'myToken', '[]', 0, '2024-12-22 12:36:36', '2024-12-22 12:36:36', '2025-12-22 13:36:36'),
('45a9a6754403d8e46403a56f83935c592eedb9d0611c0a5967884171917835b0d6266fb9f2b67e30', 33, 1, 'myToken', '[]', 0, '2024-12-22 12:34:24', '2024-12-22 12:34:25', '2025-12-22 13:34:24'),
('45aac6b5010cb8edad3ab52ad337379e4d80fe41a8c0bb6066eda28b27cfe254ed970e34959e0b31', 15, 1, 'myToken', '[]', 0, '2024-12-22 08:16:02', '2024-12-22 08:16:02', '2025-12-22 09:16:02'),
('45e5a03e622734aee33cb75bb4cc700831eec060c0884ecef0a4861492dba6a046107a68b8f42d6f', 9, 1, 'myToken', '[]', 0, '2024-09-24 09:01:37', '2024-09-24 09:01:37', '2025-09-24 12:01:37'),
('47fa286f4d609dd9ab44ec4873a262c97f1d95525ffc9c8b46289994c83cad457c354de6cc65b9e3', 24, 1, 'myToken', '[]', 0, '2025-01-27 08:23:03', '2025-01-27 08:23:03', '2025-01-28 09:23:03'),
('4846cc014741749f3fd75d65fdeccdfa2696c30c33ca4d86b3fae36dba3107d28aa8d8aae0991d89', 17, 1, 'myToken', '[]', 0, '2024-12-19 10:16:29', '2024-12-19 10:16:29', '2025-12-19 11:16:29'),
('4848401a50b510c7baed9a9f465fb85d2c4a038c7c30e0768928192528f1b339b69f28b4921ad191', 15, 1, 'myToken', '[]', 0, '2024-12-18 12:54:49', '2024-12-18 12:54:49', '2025-12-18 13:54:49'),
('48e9ae077e610acc2c4b9325b9e3dcba01039832dd777bd1b1f6a47a3f17bbdc4f748dec23f1c3ed', 16, 1, 'myToken', '[]', 0, '2024-12-18 08:42:27', '2024-12-18 08:42:27', '2025-12-18 09:42:27'),
('48f517f8b64109d641a81a99da7963ff9c10bb8beea0104dca6e50a68a6dc1ec7e96c89bc0628db6', 15, 1, 'myToken', '[]', 0, '2024-12-19 10:53:42', '2024-12-19 10:53:42', '2025-12-19 11:53:42'),
('4aa719e0d456b695620aa5c8e9fc470a6b6fe22a2c7549b7558084a9e921b9c059f8fa8fb24e7c7f', 1, 1, 'myToken', '[]', 0, '2025-01-07 09:08:51', '2025-01-07 09:08:51', '2025-01-08 10:08:51'),
('4bdf213726b70824c89103a91588f13c299875423196f8eb5bfed7478e38b3aa0d98c7608c7a3ffc', 33, 1, 'myToken', '[]', 0, '2025-01-19 11:53:34', '2025-01-19 11:53:34', '2025-01-20 12:53:34'),
('4c064469244546982323f740d0392fc593e4325f8d9364edab47da33e3813e94cfddfbf0383e45d1', 39, 1, 'myToken', '[]', 0, '2025-01-02 08:53:18', '2025-01-02 08:53:18', '2025-01-03 09:53:18'),
('4f16cd4f4a837569314cb2d992468cf5fd0e05a0c91934c3a97704f5104d9649a8de96e224874142', 33, 1, 'myToken', '[]', 0, '2025-01-22 07:16:46', '2025-01-22 07:16:46', '2025-01-23 08:16:46'),
('500ed5c7a25e11e3962718ae1d364412256fbcfe465f756919915a4c02dc311cddd79a02f466765a', 33, 1, 'myToken', '[]', 0, '2024-12-26 09:03:58', '2024-12-26 09:03:58', '2025-12-26 10:03:58'),
('5026136d8176d1717cdf6962593a1340dc91f6a0f466838432e6464e09ea8d050be55ec7a6b90b04', 33, 1, 'myToken', '[]', 0, '2024-12-22 12:35:37', '2024-12-22 12:35:37', '2025-12-22 13:35:37'),
('5117118831d191e6dc3a4e7548f500c7ca069bd82e76732cca5711d447de2c5aca662f43ad8fb5b9', 69, 1, 'myToken', '[]', 0, '2025-01-20 14:17:35', '2025-01-20 14:17:35', '2025-01-21 15:17:35'),
('52ab1b9ccf3df1727ea2ec4422a203c47e6ab7c5a176dbd3a964e32c215679d766b3f53f29113664', 69, 1, 'myToken', '[]', 0, '2025-01-20 14:45:01', '2025-01-20 14:45:02', '2025-01-21 15:45:03'),
('52f6087c63dd5282bac0a8a34c8af38a5f370a18532b28eacbacc7d5078a7acccb4611a92fde94e1', 1, 1, 'myToken', '[]', 0, '2025-01-01 09:20:51', '2025-01-01 09:20:51', '2025-01-02 10:20:51'),
('539481dd0d3defc4cb7e8051fd1b2e3878ba2b343d1206dda6788515cd7513fdc88748c233c338d3', 10, 1, 'myToken', '[]', 0, '2024-10-17 08:18:00', '2024-10-17 08:18:00', '2025-10-17 11:18:00'),
('5ba0424f64e32aff1cb55da4786f2486e3ac587e31bac524856956dea4d8d9d5444f41df2f7c1c7b', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:11:53', '2024-12-19 12:11:53', '2025-12-19 13:11:53'),
('5d054196a97f2814c80c9b3fccd9e1a0806e181c0b27799eaf780fea3a07d308d77c8e6ccecff3d1', 30, 1, 'myToken', '[]', 0, '2024-12-24 10:59:16', '2024-12-24 10:59:17', '2025-12-24 11:59:16'),
('5e19504b7a879344ffbc8117e77b6d0b85f9ddd231cf8594fda5610782a6da6cbfd50bc415fefadc', 16, 1, 'myToken', '[]', 1, '2024-12-18 08:24:45', '2024-12-18 08:27:33', '2025-12-18 09:24:45'),
('5fea4b6d08de9bf97733381856a22fd9db993ca62413d115b5e0a8e953428a388edee07bf12d0e24', 65, 1, 'myToken', '[]', 0, '2025-01-29 10:26:31', '2025-01-29 10:26:31', '2025-01-30 11:26:31'),
('61dc6d673fa84d79ca694a8bb78e17a5b0e48f203147ac7e46d680c780c9d93424f6e98af1580027', 33, 1, 'myToken', '[]', 0, '2025-01-27 09:04:53', '2025-01-27 09:04:53', '2025-01-28 10:04:53'),
('639405b3ea59a780f6d39146331ce2ee5b1456bb8ba6e63bacb3f6728f63cecb661cfa12dd172a2c', 27, 1, 'myToken', '[]', 0, '2025-01-05 08:50:38', '2025-01-05 08:50:38', '2025-01-06 09:50:38'),
('642786e63bf94dec3faef3fb73a7ca3fd7eda7c706776da334df0f28717daa3b11eeb3a09dfb52e5', 69, 1, 'myToken', '[]', 0, '2025-01-20 14:32:27', '2025-01-20 14:32:27', '2025-01-21 15:32:27'),
('67d5e84cfccd48b1ffc8c0f0d5051717fc450bdb9a1a8879a402c9721fb2e64c2c546b9c72878e89', 34, 1, 'myToken', '[]', 0, '2024-12-19 12:59:52', '2024-12-19 12:59:52', '2025-12-19 13:59:52'),
('68b55a312ea760cceeac9eef1bb03b62b353d486819f0d8d4d2ae34e5f911a5b08cfa812c26c89ed', 33, 1, 'myToken', '[]', 0, '2024-12-23 10:29:32', '2024-12-23 10:29:32', '2025-12-23 11:29:32'),
('6961bb2dcaf9bb0bea99f44672cdd22c543b3a0b08ae28947c6ec181934b8f097d4373c11c65b28d', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:58:29', '2024-12-19 12:58:30', '2025-12-19 13:58:29'),
('698bf001ede1683556f30b0ca30131332df70c61867692aab286b5fd8b480ee0a95a976d5ee1c324', 33, 1, 'myToken', '[]', 0, '2025-01-19 11:10:47', '2025-01-19 11:10:47', '2025-01-20 12:10:47'),
('6aca5de02ca48d7036e8363262e4681d3b9f0178755a8d2a7295c6067da791b3e8edcff8a10ceb5f', 1, 1, 'myToken', '[]', 0, '2025-01-01 12:57:45', '2025-01-01 12:57:45', '2025-01-02 13:57:45'),
('6aebf9ead457140d34fd4325b46160460667e9f6859292d68160e93e124b4664b2853d08f4d938fa', 37, 1, 'myToken', '[]', 0, '2025-01-19 11:38:22', '2025-01-19 11:38:22', '2025-01-20 12:38:22'),
('6b466550f90d98ec457d06d8cafac369d68533e48de952c1de855c657040e8e3cb94e50a68fde7b4', 1, 1, 'myToken', '[]', 0, '2025-01-05 08:54:56', '2025-01-05 08:54:56', '2025-01-06 09:54:56'),
('6cea93b335176c83f4cc5d29a7cd76fa83c26661d8a113d450e6ea451f3c4a53abf578a1ce4212ca', 1, 1, 'myToken', '[]', 0, '2025-01-01 11:05:48', '2025-01-01 11:05:49', '2025-01-02 12:05:49'),
('6d684b02a9dd839549e972fc241f320757062240fd0179bd7e3680b200f2624ffb9b933bcd524d7d', 37, 1, 'myToken', '[]', 0, '2025-01-22 13:18:02', '2025-01-22 13:18:02', '2025-01-23 14:18:02'),
('6e994a73d294cb18edce94829bdd50fcd57ffe36072b3b886fef36ee993f27f6be0dc4b2bbed9991', 33, 1, 'myToken', '[]', 0, '2025-01-26 10:08:12', '2025-01-26 10:08:12', '2025-01-27 11:08:13'),
('6eb53dabc3575136d412ac1147370806aecd10634bd2bb0fd59c0bb3f33a20d8b60d5f50476bba68', 39, 1, 'myToken', '[]', 0, '2025-01-05 06:21:19', '2025-01-05 06:21:20', '2025-01-06 07:21:20'),
('6f3d6cbad1efc9cc775f8001bbabc145de908800903882ca6ecf65b56b0b87ef69fe3fdcf06a7b96', 33, 1, 'myToken', '[]', 0, '2024-12-26 11:26:31', '2024-12-26 11:26:31', '2025-12-26 12:26:31'),
('6fbaf28786a4ea7175ff107a6f0068d7e9e7b0ed787992481330eb9dd0db63514e11b7be2eab7e9d', 37, 1, 'myToken', '[]', 0, '2025-01-23 13:56:05', '2025-01-23 13:56:05', '2025-01-24 14:56:05'),
('6fd9e304edd3b478db9b480a33124bac2e5d44921b7fa28c92e2e32815b99d57abeade107a95cebe', 34, 1, 'myToken', '[]', 0, '2024-12-19 12:59:46', '2024-12-19 12:59:47', '2025-12-19 13:59:46'),
('70b9ff99e01e22ed975023af81dd4c4f082cff0456adb5f88e9460459602dfd0b5377e975427d4eb', 17, 1, 'myToken', '[]', 0, '2024-12-22 06:24:17', '2024-12-22 06:24:17', '2025-12-22 07:24:17'),
('70c70f025fdaa3cb8c9487f2e999917613630c86f1979d7ab791720ff6dd9a9c51484c83848cfa29', 17, 1, 'myToken', '[]', 0, '2024-12-18 13:21:05', '2024-12-18 13:21:05', '2025-12-18 14:21:05'),
('72abe167cea3be485eb989adf26a4613bbc734e58b34851c70306f95686c13c4009f2e69a34c3d22', 33, 1, 'myToken', '[]', 0, '2024-12-24 09:42:14', '2024-12-24 09:42:14', '2025-12-24 10:42:14'),
('72dd9be25468f6fd8b3ed62f2db0889d02fb793f7c2ce03f227125c93558175087797badcb9560b1', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:09:00', '2024-12-19 12:09:00', '2025-12-19 13:09:00'),
('730646f325dfbd2231db79b0bea8acb778b6eed27fdedd99b254e1c742c934c3d16b94d5b10c07b6', 37, 1, 'myToken', '[]', 0, '2025-01-23 14:51:29', '2025-01-23 14:51:29', '2025-01-24 15:51:29'),
('7352a4f2fb525afc7d8ff6b4ccceba24fd26977369929aa7708bc1fc3811913b9fc30accd2db9753', 37, 1, 'myToken', '[]', 0, '2025-01-19 11:38:02', '2025-01-19 11:38:02', '2025-01-20 12:38:02'),
('74b4088641764b3f618f62b41fa1d3698df0bfd1ad6a2bd7c6256a618609364f755ace5c3fdae23a', 33, 1, 'myToken', '[]', 0, '2024-12-29 05:21:33', '2024-12-29 05:21:33', '2025-12-29 06:21:33'),
('75e7de2a2ef7ab9bf8015f6cdf9b66924d6967a1e53d244c0ed900249af93b6952ac7ebbb778a86b', 17, 1, 'myToken', '[]', 0, '2024-12-19 06:52:45', '2024-12-19 06:52:45', '2025-12-19 07:52:45'),
('76da7ba273d8cec32b4a30468ce4b208ed5d2e1a6e1186bc3b437a0e381c4978a0186838c0fdfee1', 33, 1, 'myToken', '[]', 0, '2025-01-28 10:02:01', '2025-01-28 10:02:01', '2025-01-29 11:02:01'),
('76df4323efd364862da4255cd63f7ed33d4afa5d5683e2cd7efd9c1930bdd3f8c239b929810d76cb', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:57:26', '2024-12-19 12:57:27', '2025-12-19 13:57:26'),
('7743d583b36b2223e816100ef3c3e79b45f4f629325860a28945a1bb610c35afa967aaa6ca69012b', 39, 1, 'myToken', '[]', 0, '2025-01-02 12:42:22', '2025-01-02 12:42:23', '2025-01-03 13:42:23'),
('775b87b1994d0269eebcf5d67c5ea005b87fd64ec71a434ce6f1fdc210629b08fb44a791f28ce6fc', 39, 1, 'myToken', '[]', 0, '2025-01-20 08:26:03', '2025-01-20 08:26:03', '2025-01-21 09:26:03'),
('7799a39c641bc4cb61484b489d952b90115990d834fdb28f9d9c0533de260741a4c46a7dc6fe9103', 1, 1, 'myToken', '[]', 0, '2025-01-01 12:33:25', '2025-01-01 12:33:25', '2025-01-02 13:33:25'),
('77be2545ae516efdaf3e8978340877bc0d723c8809a5b9cd78081ba7d265b765e69ecdb8acc79a20', 33, 1, 'myToken', '[]', 0, '2024-12-26 07:55:27', '2024-12-26 07:55:27', '2025-12-26 08:55:27'),
('77d9fe11cdfccf18fd6558f329df0992de5497ae799dc495f6f689e6b17c97f5d5cd4d65d688884c', 33, 1, 'myToken', '[]', 0, '2024-12-24 05:10:40', '2024-12-24 05:10:41', '2025-12-24 06:10:40'),
('7abcafaf60a94b52785b82309d966f5e01b1c2eca488775251ed88fc7f78d083fd1b225e8d050b40', 15, 1, 'myToken', '[]', 1, '2024-12-17 12:50:57', '2024-12-17 12:51:17', '2025-12-17 13:50:57'),
('7ac7e9aa61c673089bec90fbdb3a5cefe5c4e5d4d4084d11e2168feb8ff4263d1bf8137a20bb0a9c', 1, 1, 'myToken', '[]', 0, '2024-12-26 07:23:11', '2024-12-26 07:23:12', '2025-12-26 08:23:11'),
('7c2e7886600a167f4a543040eb4f32e8fd835e1a55e40ad63dc3034ff6b27d7e9bb01ae64432279b', 39, 1, 'myToken', '[]', 0, '2024-12-26 07:51:20', '2024-12-26 07:51:20', '2025-12-26 08:51:20'),
('7cf540dbd253a2d4dd29a154da68156d335ece2bf695fdfefb299ffb835eee0e44569ceb25e1b989', 18, 1, 'myToken', '[]', 1, '2024-12-18 08:43:41', '2024-12-18 08:44:36', '2025-12-18 09:43:41'),
('7d8125e5de6fb44a659df171f9fa2e99f965406e474286ad2f01cf41a4fab1497767c6b16d1b579e', 39, 1, 'myToken', '[]', 0, '2024-12-25 07:56:20', '2024-12-25 07:56:20', '2025-12-25 08:56:20'),
('7d95b5ea3a4e0c71b4522b8ca18cbb4e0587e42c17b9dcbd629d10c4fc774c3b96727826265d32a3', 24, 1, 'myToken', '[]', 0, '2024-12-19 06:48:11', '2024-12-19 06:48:11', '2025-12-19 07:48:11'),
('7f549d0cecfdf621c28889840b4649b85e000d6e1e349acc4aeaa42b7bb7baa90cc18a5fc8df524a', 16, 1, 'myToken', '[]', 0, '2024-12-18 08:36:36', '2024-12-18 08:36:36', '2025-12-18 09:36:36'),
('883a0761729da083054e441c671deb28bc71786d8c2f93197cd4c5dff4d78582c2c8c9327573ea69', 33, 1, 'MyApp', '[]', 0, '2024-12-19 12:58:10', '2024-12-19 12:58:10', '2025-12-19 13:58:10'),
('896642d7fa9e723db4bf0f3c05548dc9e1fe34817f69e8927c48513f2d078eb34d8cac7462ab7402', 33, 1, 'myToken', '[]', 0, '2024-12-29 07:50:37', '2024-12-29 07:50:37', '2025-12-29 08:50:37'),
('89dc7de07efa565f544fd226ff26f013268d43063c0797c3e187680632608900d7effbb9e49ed010', 24, 1, 'myToken', '[]', 0, '2024-12-19 06:43:59', '2024-12-19 06:44:00', '2025-12-19 07:43:59'),
('8c58a71955a840869ca1fadfb8e1dc76d87764c668ae37207bc88fe9d0b8310392537f50cb1f5f37', 33, 1, 'myToken', '[]', 0, '2025-01-05 13:15:49', '2025-01-05 13:15:49', '2025-01-06 14:15:49'),
('8df2588775516938d497054af000fdef374c7aeb59a012aa65e4976c2a363d5c377bc451432d8844', 17, 1, 'myToken', '[]', 0, '2024-12-18 11:12:40', '2024-12-18 11:12:40', '2025-12-18 12:12:40'),
('8f21043153c1d5007349ac8eb6637f947e8ef655635ad35c3e667caa16f6560c789654a24e270f83', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:58:27', '2024-12-19 12:58:27', '2025-12-19 13:58:27'),
('932def80c140b305d0d7fb7e2499bbe24b0ab5d72542e4bd66350338953ba4c7a8d5d7bac55b49e1', 33, 1, 'myToken', '[]', 0, '2025-01-05 13:16:07', '2025-01-05 13:16:07', '2025-01-06 14:16:07'),
('9375045ee67fef20dba544a777e3bd948c103150530c3226acbf713f6a5c09cf741e98ad7c6038a2', 13, 1, 'GoogleAuthToken', '[]', 0, '2024-11-04 10:19:43', '2024-11-04 10:19:43', '2025-11-04 12:19:43'),
('93e0edd102f26f58bec37fb690626defa31c169acbf6e28de31b2d4b5b79a2e371fc141e6050bc14', 33, 1, 'myToken', '[]', 0, '2025-01-28 10:27:37', '2025-01-28 10:27:37', '2025-01-29 11:27:37'),
('978e8129fa66ea43aa8ffd0d96d0be6c1e24da295bedcf0b26233a9d2f013022d3e67a063e656e17', 33, 1, 'myToken', '[]', 0, '2024-12-22 12:36:30', '2024-12-22 12:36:30', '2025-12-22 13:36:30'),
('9a3e1632ef4faccb76d136f98281df3a7313cc92c9115db192abeefe72ef3f1c6a4763642f976e2e', 1, 1, 'myToken', '[]', 0, '2025-01-07 08:24:57', '2025-01-07 08:24:58', '2025-01-08 09:24:58'),
('9b0af7f6c4e9d9d639a52d9003d5991d32e8b919bedc8a6cbea666e502438ef2307a91e35f425868', 15, 1, 'myToken', '[]', 0, '2024-12-17 12:02:26', '2024-12-17 12:02:26', '2025-12-17 13:02:26'),
('9c1f9c250fdbbe14e8b2b5f5f8820407542cebbfb133a3cb31961b3bdd95a5110c2d06fc6a6ba95c', 19, 1, 'myToken', '[]', 0, '2024-12-19 13:09:42', '2024-12-19 13:09:42', '2025-12-19 14:09:42'),
('9c4bce0547a1bc86c02bd626bfd7942fa57c2524cebb3cc1c4f9039b9230c7793339f420ba1d4721', 17, 1, 'myToken', '[]', 0, '2024-12-19 06:35:59', '2024-12-19 06:35:59', '2025-12-19 07:35:59'),
('9ddfcf1e6a00019cb680de10d8dfacbddaf550cf4814f2b40e771cd1594d996e067cfc59120317b5', 33, 1, 'myToken', '[]', 0, '2024-12-22 08:18:03', '2024-12-22 08:18:03', '2025-12-22 09:18:03'),
('9e20bbf264322827b111d752625968fc83c1e955657f1c77094e4d3110d48f477097a49608f91024', 33, 1, 'myToken', '[]', 0, '2025-01-27 08:16:51', '2025-01-27 08:16:52', '2025-01-28 09:16:52'),
('9e980adb3378790506663f30005d1807755f89cc65b2ce3b3312eff50eacf19d4799020a06777059', 39, 1, 'myToken', '[]', 0, '2025-01-02 06:49:25', '2025-01-02 06:49:25', '2025-01-03 07:49:25'),
('9f98999a412d4d03a17778043c6f45e13dd26945dc19cbce45d8a1466f2f030780d1f5ceb6f81dc1', 24, 1, 'myToken', '[]', 0, '2024-12-19 07:15:13', '2024-12-19 07:15:13', '2025-12-19 08:15:13'),
('9fdf96429693a00d2bbdc0c1b8fb7eb3d98773ab29dde0bbf36ec7924ab2e7af7aac101770ba8575', 39, 1, 'myToken', '[]', 0, '2025-01-02 12:46:57', '2025-01-02 12:46:57', '2025-01-03 13:46:57'),
('9fffe8ad5277668ef3641820a2325ee47811a26cafbb8d7dddb5848a0ad5304ceb2516a6f5044f1e', 27, 1, 'myToken', '[]', 0, '2024-12-22 10:11:27', '2024-12-22 10:11:28', '2025-12-22 11:11:27'),
('a16164f7f4751dc0359921ed733befbba4069406de11bf503766c2a9ee5ffe475c8ac3e1878a185a', 33, 1, 'MyApp', '[]', 0, '2024-12-19 12:56:23', '2024-12-19 12:56:23', '2025-12-19 13:56:23'),
('a209f2e3ecc43adcdc86ca1e6b4d042a4cfc90a6ec4257a69df2e30c3674542b374cf67190298e4e', 15, 1, 'myToken', '[]', 0, '2024-12-15 09:30:30', '2024-12-15 09:30:30', '2025-12-15 10:30:30'),
('a2444be56c02fe4e311e6ea5aaa7d375f9773acb2cfc13ec4cf375650e8ccee1ef459f7446e25944', 24, 1, 'myToken', '[]', 0, '2025-01-26 13:49:32', '2025-01-26 13:49:32', '2025-01-27 14:49:32'),
('a2b26345e51912b6d6fd31841eeb7453b520868c101eeaad3ad1706de29a0a8c970634b4954ced30', 37, 1, 'myToken', '[]', 0, '2025-01-19 14:20:34', '2025-01-19 14:20:34', '2025-01-20 15:20:34'),
('a7620501cc12ede4f85262d55957074052d16971dd019a911d703f435be57e3ca87171a9f2b6369f', 37, 1, 'myToken', '[]', 0, '2025-01-20 09:39:42', '2025-01-20 09:39:42', '2025-01-21 10:39:42'),
('a950ecd50fa7dc0502d2b840c5921734b3f84c655b9dba7fd22288b22bff4819a39b7224216898c3', 15, 1, 'myToken', '[]', 0, '2024-12-17 10:47:44', '2024-12-17 10:47:44', '2025-12-17 11:47:44'),
('ab125cd41c8064068d8ba22e7906d95a76422d6b30264b697e4f4bec07209c0ccdb0f1436da835e6', 17, 1, 'myToken', '[]', 0, '2024-12-18 11:10:55', '2024-12-18 11:10:55', '2025-12-18 12:10:55'),
('ad1340d822a8a47970a2b6b73211a570b43033ee0371f533e0b309c6d7ef6ce2da4cfcdfee23b1df', 15, 1, 'myToken', '[]', 0, '2024-12-19 07:30:07', '2024-12-19 07:30:07', '2025-12-19 08:30:07'),
('ad174d548520aa684d25467a344cd1c9556e3703fa9ec172e815cd59da6dd918b75cf917603357f0', 39, 1, 'myToken', '[]', 0, '2025-01-16 16:04:12', '2025-01-16 16:04:12', '2025-01-17 17:04:12'),
('ae4e0a2c08935b57cf4bc30e46cb47f3bc054beef226acaae7d6155fdfad432498635c13d8b5a7a9', 18, 1, 'myToken', '[]', 0, '2024-12-18 08:44:43', '2024-12-18 08:44:43', '2025-12-18 09:44:43'),
('b121e4c531a688f89a0a774751da9b4d2395ac5ff9dbeee285955a02df8a85e66bb1cde977599c5d', 15, 1, 'myToken', '[]', 0, '2024-12-17 11:30:30', '2024-12-17 11:30:30', '2025-12-17 12:30:30'),
('b18f32c8c5f3a90ee7557ba7f08d07c8227055c819c039c8cad8a7150b804a6d2154e9ce3ab36605', 33, 1, 'myToken', '[]', 0, '2025-01-09 10:55:17', '2025-01-09 10:55:18', '2025-01-10 11:55:18'),
('b1fd29a58f583e45b638b4cbbb32b7747e75f26b20af66e66e35ace5ecf5bdae17bf2177e7027c19', 37, 1, 'myToken', '[]', 0, '2025-01-19 11:37:55', '2025-01-19 11:37:56', '2025-01-20 12:37:56'),
('b2eeb541368535f5a9608654f772c04d04604cbbd8ea33357af4e559acb9d2c91103261ca6878bf2', 15, 1, 'myToken', '[]', 0, '2024-12-18 11:54:46', '2024-12-18 11:54:46', '2025-12-18 12:54:46'),
('b408ce90ec0c7476df4ef2ed87f4f633d926ad545bf168e42cd9854272ef0f01d7d22401e7e0f23f', 15, 1, 'myToken', '[]', 0, '2024-12-19 10:13:58', '2024-12-19 10:13:58', '2025-12-19 11:13:58'),
('b478834141a97672e671e6d35b6a06ac3ee60982fc7934dd6ed06e5fd2ba24933070b5a3ab28dc58', 35, 1, 'myToken', '[]', 0, '2024-12-22 08:28:52', '2024-12-22 08:28:52', '2025-12-22 09:28:52'),
('b5a90fbbe10f365016232539363b64f513a83d86712049f4d4208a532e88377c14d87dfc441e3de4', 33, 1, 'myToken', '[]', 0, '2024-12-22 12:36:14', '2024-12-22 12:36:14', '2025-12-22 13:36:14'),
('b8dae25ab4464591c9a41ee2461ef9c4db5dcda048590c8887a53166310d249e975236a01272dd58', 26, 1, 'MyApp', '[]', 0, '2024-12-19 10:08:57', '2024-12-19 10:08:57', '2025-12-19 11:08:57'),
('b943de93d89752d7cb95e4a172bd576710c5462e33fc9d6c9a2cc20a8316e6ed075d1ff9c2653eca', 33, 1, 'myToken', '[]', 0, '2025-01-05 12:25:18', '2025-01-05 12:25:18', '2025-01-06 13:25:18'),
('b97a268729095c3857c97646393fd02e0c3542bd18f5897aa06d2055275e351f4712f0b9d2719f1d', 17, 1, 'myToken', '[]', 0, '2024-12-19 05:23:01', '2024-12-19 05:23:01', '2025-12-19 06:23:01'),
('ba2caa9400ab9e907dfbc6ddfc7c68e6e01a59f22e6503f49310bbaf90100e01347215c5c924ef6a', 17, 1, 'myToken', '[]', 0, '2024-12-18 08:38:02', '2024-12-18 08:38:03', '2025-12-18 09:38:02'),
('bb3f85e3c51e45f922381d17d8e0f19d8127fe96485c690609a10a2f94d72410eb7abaf7b2a56d94', 33, 1, 'myToken', '[]', 0, '2024-12-22 12:36:03', '2024-12-22 12:36:03', '2025-12-22 13:36:03'),
('bc2e95eeb565231e8dbf6b2aa0e36f194c118f43704ccadd1f45a7cb84baa23d095b4bc06e781081', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:09:05', '2024-12-19 12:09:05', '2025-12-19 13:09:05'),
('bd10a2d94cd2d72665acaa4e487a9a9fb076e1c3f34ab97a8c3b7148702840c133ce6bc2fc32580a', 17, 1, 'myToken', '[]', 0, '2024-12-19 13:19:01', '2024-12-19 13:19:01', '2025-12-19 14:19:01'),
('bd677372562cb0c8b5fd75d992ba7e84a81e45c1933875229623cd18b77dfe34389e91d8269f814e', 33, 1, 'myToken', '[]', 0, '2024-12-23 06:10:30', '2024-12-23 06:10:30', '2025-12-23 07:10:30'),
('c067c13ac605b7ad7c23f857e20031b2f3ad46fc43cf656b7e0dc2d7aa763457ecd2481e350b119f', 33, 1, 'myToken', '[]', 0, '2025-01-27 07:17:13', '2025-01-27 07:17:13', '2025-01-28 08:17:13'),
('c2d9c7c06c8c535a9f469db255dc11b0bc55daaf3418400c0392ca0bc65763fa6b0852364ca68b7a', 33, 1, 'myToken', '[]', 0, '2025-01-23 11:28:56', '2025-01-23 11:28:57', '2025-01-24 12:28:57'),
('c51cb6a990b2a9c7567bf958340171cd8ca9aac4d984132616d20b504cd0617bfaea43159530256a', 65, 1, 'myToken', '[]', 0, '2025-01-30 07:34:46', '2025-01-30 07:34:46', '2025-01-31 09:34:46'),
('c55643cdd3098976ad8e115dee408fe603ba75bb3b010c6ae9e37ae445e6cfd533deac8a4f514652', 33, 1, 'myToken', '[]', 0, '2024-12-26 11:25:36', '2024-12-26 11:25:36', '2025-12-26 12:25:36'),
('c596e267c7c1c6f87d4162bc98bec687b4b86a7debc10b7e42c8f8b9230b6d5f15d2661c74267294', 15, 1, 'myToken', '[]', 0, '2024-12-19 10:13:21', '2024-12-19 10:13:21', '2025-12-19 11:13:21'),
('c5c7ad40c4744fd36914a29f03c900864ccac2d4f38fb6424b05473eee14e1e9106c9cdeab9c76e1', 33, 1, 'myToken', '[]', 0, '2025-01-20 12:33:50', '2025-01-20 12:33:50', '2025-01-21 13:33:50'),
('c5c95b56c36035c8b84d7ab350f1d15b9004366b869f35a7ef3042bcabd4e96b3d6e4a0cff4c907e', 33, 1, 'myToken', '[]', 0, '2024-12-29 10:29:53', '2024-12-29 10:29:54', '2024-12-30 11:29:54'),
('c7905c6a4baf181ccab6fbe4bfdc7318eebfda2551051bf76b78202cafa78769cb9cb9fd877e47d2', 33, 1, 'myToken', '[]', 0, '2025-01-27 09:15:43', '2025-01-27 09:15:43', '2025-01-28 10:15:43'),
('c7aba5ec9e75eb1be04cf2616a7517faf8dcf8bc97b4a735f7d45e7cd2f7b4c67beef3e4f0c8f481', 33, 1, 'myToken', '[]', 0, '2024-12-29 06:34:30', '2024-12-29 06:34:30', '2025-12-29 07:34:30'),
('c980b9aa5db677b82741cbfaf6fe259765772fb91341235a296404c13c20ea06c8ec7e94e7301f19', 33, 1, 'myToken', '[]', 0, '2024-12-25 11:44:23', '2024-12-25 11:44:24', '2025-12-25 12:44:23'),
('c9b2df9a8bfe7b062d86e2f418a3765a8e4e62039592f01a950934bce55d9bbbdf0fbada3653821e', 33, 1, 'myToken', '[]', 0, '2024-12-30 10:03:55', '2024-12-30 10:03:55', '2024-12-31 11:03:55'),
('ce52a433c8acb4b378e5fb5c61fc32ab6d56b5ab970deeaf2492fe4a287bd2c5b0612fbcd8c9f119', 65, 1, 'myToken', '[]', 0, '2025-02-02 08:15:05', '2025-02-02 08:15:05', '2025-02-03 10:15:05'),
('cf208910af84c0af7a5a2c5049af6a960fbd5a2582f8e58a47ae405b9d676f3ccf69572fcf41bb58', 1, 1, 'myToken', '[]', 0, '2024-12-29 10:20:04', '2024-12-29 10:20:04', '2024-12-30 11:20:04'),
('cfe01c4445a0bc9f0399972847c3de77397f4375138cc1c749188b4f5ffc89587ef653adcad29562', 33, 1, 'myToken', '[]', 0, '2025-01-26 08:42:36', '2025-01-26 08:42:36', '2025-01-27 09:42:36'),
('cff9df811eb94d3b56bd467b4ecd1a82f83da14add7e6ee409f139a98b843e9320da2c29c4f1f133', 10, 1, 'myToken', '[]', 0, '2024-10-17 08:20:39', '2024-10-17 08:20:39', '2025-10-17 11:20:39'),
('d717da5b25c294a54e858d35a0b8fcd3b15be60de0b0d53e03b32ae6971d63956782a084eb6d7779', 39, 1, 'myToken', '[]', 0, '2025-01-21 09:02:24', '2025-01-21 09:02:24', '2025-01-22 10:02:24'),
('d7a2576a1f1a5a544b3c070156fc96dd13133b97bce824cf49780d33c57affebb6d965c9f410d5b9', 39, 1, 'myToken', '[]', 0, '2025-01-16 16:03:55', '2025-01-16 16:03:55', '2025-01-17 17:03:55'),
('d908c0c8cf68a5186608cd733748cd1698b8b95b61f836cbe34610ae306ee4c95348d681033e4876', 37, 1, 'myToken', '[]', 0, '2025-01-19 12:20:29', '2025-01-19 12:20:29', '2025-01-20 13:20:29'),
('d9895295bf645b7edb8633b9d5c9edcebb147da8162288cd69903314b094f88a1d5fd4fe6b793ab5', 19, 1, 'myToken', '[]', 0, '2024-12-18 13:18:48', '2024-12-18 13:18:48', '2025-12-18 14:18:48'),
('dae4508416a0b1b5b3c2dc6cebdbd9a5a782f36a6f631473b2ab1d08b214d04e63406afc34bb220d', 39, 1, 'myToken', '[]', 0, '2025-01-02 06:43:01', '2025-01-02 06:43:01', '2025-01-03 07:43:01'),
('db8f041031cf967936cfdd3de736dfd6e150b2e02f51ce5312910eff943cdbb05a225ae7d6382f90', 24, 1, 'myToken', '[]', 0, '2024-12-19 06:47:57', '2024-12-19 06:47:57', '2025-12-19 07:47:57'),
('ddab9fb18831329eb4da1022cbed11ff9189025f18aebe4b61402ce21782e3a14d9bc9a85eebd357', 27, 1, 'myToken', '[]', 0, '2024-12-22 07:06:03', '2024-12-22 07:06:03', '2025-12-22 08:06:03'),
('ddb3a5dd0c95c8c1f42d1d4a734cd7e512fa99566410d4dfd73bf8b05b5f999c86f54ca4a4c2d110', 39, 1, 'myToken', '[]', 0, '2025-01-27 08:51:53', '2025-01-27 08:51:53', '2025-01-28 09:51:53'),
('dee58c68dc8bd85d48030ddd5f537573438ba8c468eeb90095f43fbaf7e85a743fb2c48763e94fab', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:12:34', '2024-12-19 12:12:34', '2025-12-19 13:12:34'),
('e08cb45b97f8464961900ecfd12e58a184012b2d18e7da000fe975f7869d16408bed1d96bafeede6', 33, 1, 'myToken', '[]', 0, '2025-01-27 09:05:27', '2025-01-27 09:05:27', '2025-01-28 10:05:27'),
('e1694ed3868eca610782ebf2d7d521e28cfb2dd4b2b8ebdf0fc25dc8bd9ea36fcccf9db7696db662', 16, 1, 'myToken', '[]', 0, '2024-12-18 08:21:42', '2024-12-18 08:21:42', '2025-12-18 09:21:42'),
('e1884d5e2855f0caba1a93093eb45d6ace444be2adbf4d055396034348a525c659f8786e5acdda61', 17, 1, 'myToken', '[]', 0, '2024-12-19 13:03:16', '2024-12-19 13:03:16', '2025-12-19 14:03:16'),
('e23d1f771724cde44b9409f6000a5b85a7ba164db793207cedf32bacd3863cb3cabe26b798578de9', 15, 1, 'myToken', '[]', 0, '2024-12-18 07:48:21', '2024-12-18 07:48:21', '2025-12-18 08:48:21'),
('e344424b874c48cb553dc1937b99161d74b858eead5a5f6cb23c220e42f1890d2c2396390f5e0a7b', 15, 1, 'myToken', '[]', 0, '2024-12-19 07:12:15', '2024-12-19 07:12:15', '2025-12-19 08:12:15'),
('e349f38bb861525c9057e31023259c231d85a1a51415015bf1ff33e89910e94d71d0ea1b6e60d023', 33, 1, 'myToken', '[]', 0, '2024-12-30 06:09:54', '2024-12-30 06:09:55', '2024-12-31 07:09:55'),
('e35fdac10c85973a3bac58b866dea4924c29a58dc733275b10dad01594cb9b443541265b6afb1297', 37, 1, 'myToken', '[]', 0, '2025-01-19 12:03:54', '2025-01-19 12:03:54', '2025-01-20 13:03:54'),
('e4e23a363a616d0f3d3d4f4ac4adb5a4539990cbcab78f303774eb2bcaf21746006f66464378d846', 15, 1, 'myToken', '[]', 0, '2024-12-15 09:06:26', '2024-12-15 09:06:26', '2025-12-15 10:06:26'),
('e5056963daec9b51d8735e04e3d2edc543ae983652ae608d72a99a99a5e0a9ba3fa2ed9a47b230e8', 38, 1, 'myToken', '[]', 0, '2024-12-25 07:26:08', '2024-12-25 07:26:08', '2025-12-25 08:26:08'),
('e5fa834c217457ace7718fa981a9da9566edccde1fae700571b69b771869c6f857ecafdc054f449e', 10, 1, 'myToken', '[]', 0, '2024-11-20 06:43:41', '2024-11-20 06:43:41', '2025-11-20 08:43:41'),
('e62f9d3f719c8e96aa218850525504d0fff1446c07894b277921809381dd1464d5cdba38a24523d3', 39, 1, 'myToken', '[]', 0, '2025-01-01 06:48:37', '2025-01-01 06:48:37', '2025-01-02 07:48:37'),
('e6400ff4d2907138db263f8c12d8faa11a522315aaab03d2725b067e67895248a8a8f9a32ae15c4f', 33, 1, 'myToken', '[]', 0, '2024-12-19 12:56:47', '2024-12-19 12:56:47', '2025-12-19 13:56:47'),
('e64a3c867a6e743e127a181d94b22bcbaa4c8f6a3eb83dcaa44f3e52d21ab58b59afd32e35e781b0', 88, 1, 'myToken', '[]', 0, '2025-01-29 12:18:45', '2025-01-29 12:18:45', '2025-01-30 13:18:45'),
('e73e9041b0009f24717457b118142c252b88903efecd8a98dcc9ac8c2132e1b30a816871aaf29e46', 24, 1, 'myToken', '[]', 0, '2025-01-27 08:08:10', '2025-01-27 08:08:10', '2025-01-28 09:08:10'),
('e841dd794fd20f3dd70d62d262bdcdb9a8128316173cf698b3f63cebbe890c11bc0759199a46941e', 15, 1, 'myToken', '[]', 0, '2024-12-19 10:56:08', '2024-12-19 10:56:08', '2025-12-19 11:56:08'),
('e96895f7e1809a568420efc0aa9a3bb75b21c874e71edbfca81e64e9432cb43c7eca39f17c3b5a06', 65, 1, 'myToken', '[]', 0, '2025-01-30 08:03:09', '2025-01-30 08:03:09', '2025-01-31 10:03:09'),
('ea42233b5f7ab7cb70c2758438e5d9e0f7c3617e0e724642acdb4ed8faed684623513a0d4511be3d', 9, 1, 'myToken', '[]', 0, '2024-09-25 03:00:39', '2024-09-25 03:00:39', '2025-09-25 06:00:39'),
('eddcbafa9e9acdbded9507d493a35587d48460dce067de9b601fb021d9bd9dcfe96f7a43deddfd30', 17, 1, 'myToken', '[]', 0, '2024-12-18 11:19:23', '2024-12-18 11:19:23', '2025-12-18 12:19:23'),
('f14dcf2d82342b9c37f17e5fab1c790455a6ab1ab3cd3612e003eae2593e11b32afab88af66673c9', 69, 1, 'myToken', '[]', 0, '2025-01-20 14:22:55', '2025-01-20 14:22:55', '2025-01-21 15:22:55'),
('f5197ac8768884aa98512f6f7418cd9b370ae4d043971b784f0c5290f16bbf50603f61771109a3d7', 33, 1, 'myToken', '[]', 0, '2025-01-26 13:56:01', '2025-01-26 13:56:01', '2025-01-27 14:56:01'),
('f5e78995b063afb7ae86102bd8b37851d041a72b16e3f8545bceda5b9e189702c43eec6e3046b999', 33, 1, 'myToken', '[]', 0, '2024-12-22 12:34:07', '2024-12-22 12:34:07', '2025-12-22 13:34:07'),
('f734adce510a4ac318085d9e0f4062063ebc3ee91ac2ce3e636cc6887f090d7bb020a8430291178a', 39, 1, 'myToken', '[]', 0, '2025-01-02 09:16:23', '2025-01-02 09:16:23', '2025-01-03 10:16:23'),
('f773506dfae644d005b910147de6bad40e9d5a2351d76ec39832b9802b5b5f6cdf985350c5d71e12', 37, 1, 'myToken', '[]', 0, '2025-01-19 11:51:53', '2025-01-19 11:51:53', '2025-01-20 12:51:53'),
('fa5b866c9d72499dbf23c5b67b721ab097fca0841d7314ff54116112701311f44ae5167248ed9805', 33, 1, 'myToken', '[]', 0, '2024-12-22 12:34:03', '2024-12-22 12:34:03', '2025-12-22 13:34:03'),
('fb9f3fd46af45daf13f39ace57794d7dd61b23e821f94c6c34bd0bbd4b4c6855949107a6d6ca06b6', 19, 1, 'MyApp', '[]', 0, '2024-12-18 13:19:06', '2024-12-18 13:19:06', '2025-12-18 14:19:06'),
('fcb3714f5a78789cbc43c8eeaf9833f65ed7f37bc04e0e199253e97a6a0324f1e8cf15904a19516c', 33, 1, 'myToken', '[]', 0, '2025-01-28 08:24:41', '2025-01-28 08:24:41', '2025-01-29 09:24:41'),
('fd2ff4f8416275f8c4cc978e3b09f5c5ca9e609514702d8249fc8c267e66c9a0842781d7caabb6d5', 13, 1, 'GoogleAuthToken', '[]', 0, '2024-11-04 10:24:18', '2024-11-04 10:24:18', '2025-11-04 12:24:18'),
('fe1500e23b6e4b36bf96e1478997cb3e263c4eeaddb3229497b6243a4625f284375863c033fd1ab7', 26, 1, 'myToken', '[]', 0, '2024-12-19 06:55:17', '2024-12-19 06:55:17', '2025-12-19 07:55:17'),
('fe9233e19afc0b54b68425869d569947848fe27ce88ac0d9d62715119e5289644fbad99f453264f4', 15, 1, 'myToken', '[]', 0, '2024-12-16 06:08:16', '2024-12-16 06:08:17', '2025-12-16 07:08:16');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_clients`
--

INSERT INTO `oauth_clients` (`id`, `user_id`, `name`, `secret`, `provider`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Laravel Personal Access Client', 'n0gKY3ct4ULF6rNLWgFzNTcIvDonCyNTgRlliXG0', NULL, 'http://localhost', 1, 0, 0, '2024-09-19 07:20:50', '2024-09-19 07:20:50'),
(2, NULL, 'Laravel Password Grant Client', 'wyeSMVksd9JsWObaU7Y0qwdLrA0HULrkqTgXJWtm', 'users', 'http://localhost', 0, 1, 0, '2024-09-19 07:20:50', '2024-09-19 07:20:50');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_personal_access_clients`
--

INSERT INTO `oauth_personal_access_clients` (`id`, `client_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2024-09-19 07:20:50', '2024-09-19 07:20:50');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `discount_type` enum('fixed','percentage') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_value` int DEFAULT NULL,
  `branch_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `offers`
--

INSERT INTO `offers` (`id`, `name_ar`, `name_en`, `description_ar`, `description_en`, `image_ar`, `image_en`, `is_active`, `start_date`, `end_date`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `discount_type`, `discount_value`, `branch_id`) VALUES
(22, 'offer', 'offer', 'offer', 'offer', NULL, NULL, 1, '2025-01-01', '2025-02-28', 1, NULL, NULL, NULL, NULL, NULL, 'fixed', 10, '-1'),
(23, 'offer', 'offer', 'offer', 'offer', NULL, NULL, 1, '2025-01-01', '2025-02-28', 1, NULL, NULL, NULL, '2025-02-03 13:48:32', NULL, 'fixed', 10, '10'),
(24, 'offer', 'offer', 'offer', 'offer', NULL, NULL, 1, '2025-01-01', '2025-02-28', 1, NULL, NULL, NULL, '2025-02-03 13:49:35', NULL, 'fixed', 10, '11');

-- --------------------------------------------------------

--
-- Table structure for table `offer_details`
--

CREATE TABLE `offer_details` (
  `id` bigint UNSIGNED NOT NULL,
  `offer_id` bigint UNSIGNED NOT NULL,
  `offer_type` enum('dishes') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_id` int UNSIGNED NOT NULL,
  `count` int NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `offer_details`
--

INSERT INTO `offer_details` (`id`, `offer_id`, `offer_type`, `type_id`, `count`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(22, 21, 'dishes', 60, 1, 1, 1, NULL, '2025-01-19 10:46:30', '2025-01-20 14:02:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `opening_balance`
--

CREATE TABLE `opening_balance` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `store_id` bigint UNSIGNED NOT NULL,
  `amount` double NOT NULL,
  `price` double DEFAULT NULL,
  `date` date NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `deleted_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `expired_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `status` enum('pending','completed','cancelled','inprogress') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `type` enum('Delivery','CallCenter','Takeaway','Online','InResturant') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_value` double(8,2) DEFAULT NULL,
  `delivery_fees` double(8,2) DEFAULT NULL,
  `total_price_befor_tax` double(8,2) DEFAULT NULL,
  `total_price_after_tax` double(8,2) DEFAULT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `table_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modify_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `discount_id` bigint UNSIGNED DEFAULT NULL,
  `coupon_id` bigint UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `invoice_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `time` time NOT NULL,
  `client_address_id` bigint UNSIGNED DEFAULT NULL,
  `service_fees` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `status`, `type`, `note`, `order_number`, `tax_value`, `delivery_fees`, `total_price_befor_tax`, `total_price_after_tax`, `client_id`, `table_id`, `created_by`, `modify_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `discount_id`, `coupon_id`, `date`, `invoice_number`, `branch_id`, `time`, `client_address_id`, `service_fees`) VALUES
(1, 'pending', 'Delivery', '', '#4243', 76.50, 20.00, 357.00, 407.00, 33, NULL, 33, NULL, NULL, '2025-01-21 11:53:30', '2025-01-21 11:53:30', NULL, NULL, NULL, '2025-01-21', 'INV-1-1990', 12, '12:53:30', 20, 30.00),
(2, 'pending', 'Delivery', '', '#9765', 10.00, 5.00, 80.00, 135.00, 33, NULL, 33, NULL, NULL, '2025-01-22 08:07:27', '2025-01-22 08:07:27', NULL, NULL, 11, '2025-01-22', 'INV-2-9309', 10, '09:07:27', 22, 50.00),
(3, 'pending', 'Delivery', '', '#5135', 2.40, 5.00, 19.20, 74.20, 70, NULL, 70, NULL, NULL, '2025-01-22 09:45:52', '2025-01-22 09:45:52', NULL, NULL, 11, '2025-01-22', 'INV-3-3204', 11, '10:45:52', 24, 50.00),
(4, 'pending', 'Delivery', '', '#8032', 2.40, 5.00, 19.20, 74.20, 70, NULL, 70, NULL, NULL, '2025-01-22 09:46:04', '2025-01-22 09:46:04', NULL, NULL, NULL, '2025-01-22', 'INV-4-8832', 11, '10:46:04', 24, 50.00),
(5, 'cancelled', 'Delivery', '', '#7958', 51.50, 5.00, 412.00, 518.50, 33, NULL, 33, NULL, NULL, '2025-01-23 14:49:20', '2025-01-26 07:57:36', NULL, NULL, NULL, '2025-01-23', 'INV-5-2780', 11, '15:49:20', 30, 50.00),
(6, 'pending', 'Delivery', 'order note', '#9661', 18.00, 5.00, 144.00, 217.00, 33, NULL, 33, NULL, NULL, '2025-01-27 09:21:33', '2025-01-27 09:21:33', NULL, NULL, NULL, '2025-01-27', 'INV-6-1482', 11, '10:21:33', 20, 50.00),
(7, 'pending', 'Delivery', '', '#9358', 10.00, 5.00, 80.00, 145.00, 33, NULL, 33, NULL, NULL, '2025-01-27 09:25:01', '2025-01-27 09:25:01', NULL, NULL, NULL, '2025-01-27', 'INV-7-1852', 11, '10:25:01', 20, 50.00),
(8, 'pending', 'Delivery', '', '#4403', 6.00, 5.00, 48.00, 109.00, 82, NULL, 82, NULL, NULL, '2025-01-27 11:14:25', '2025-01-27 11:14:25', NULL, NULL, NULL, '2025-01-27', 'INV-8-6005', 11, '12:14:25', 37, 50.00),
(9, 'pending', 'Delivery', '', '#5086', 5.00, 5.00, 40.00, 100.00, 87, NULL, 87, NULL, NULL, '2025-01-27 14:20:49', '2025-01-27 14:20:49', NULL, NULL, NULL, '2025-01-27', 'INV-9-2074', 10, '15:20:49', 44, 50.00),
(10, 'pending', 'Delivery', 'dish note', '#5563', 96.60, 10.00, 128.80, 335.40, 33, NULL, 33, NULL, NULL, '2025-01-29 11:09:49', '2025-01-29 11:09:50', NULL, NULL, 11, '2025-01-29', 'INV-10-3125', 10, '12:09:49', 50, 100.00),
(11, 'cancelled', 'Delivery', 'order note', '#8396', 75.60, 10.00, 100.80, 286.40, 33, NULL, 33, NULL, NULL, '2025-01-29 11:27:31', '2025-01-29 11:27:38', NULL, NULL, NULL, '2025-01-29', 'INV-11-1512', 10, '12:27:31', 50, 100.00),
(12, 'pending', 'Delivery', 'اوك', '#5032', 36.00, 10.00, 48.00, 194.00, 33, NULL, 33, NULL, NULL, '2025-01-29 12:51:03', '2025-01-29 12:51:04', NULL, NULL, NULL, '2025-01-29', 'INV-12-9338', 10, '13:51:03', 50, 100.00),
(20, 'pending', 'Takeaway', NULL, '#7186', 12.60, 0.00, 16.80, 29.40, 65, NULL, 65, NULL, NULL, '2025-02-02 08:24:18', '2025-02-02 08:24:18', NULL, NULL, NULL, '2025-02-02', 'INV-13-7045', 10, '10:24:18', NULL, 0.00),
(21, 'pending', 'Takeaway', NULL, '#1878', 12.60, 0.00, 16.80, 29.40, 65, NULL, 65, NULL, NULL, '2025-02-02 08:26:39', '2025-02-02 08:26:39', NULL, NULL, NULL, '2025-02-02', 'INV-14-8005', 10, '10:26:39', NULL, 0.00),
(22, 'pending', 'Takeaway', NULL, '#9100', 12.60, 0.00, 16.80, 29.40, 65, NULL, 65, NULL, NULL, '2025-02-02 08:40:56', '2025-02-02 08:40:56', NULL, NULL, NULL, '2025-02-02', 'INV-15-8514', 10, '10:40:56', NULL, 0.00),
(23, 'pending', 'Takeaway', NULL, '#4785', 12.60, 0.00, 16.80, 29.40, 65, NULL, 65, NULL, NULL, '2025-02-02 08:42:33', '2025-02-02 08:42:33', NULL, NULL, NULL, '2025-02-02', 'INV-16-3908', 10, '10:42:33', NULL, 0.00),
(24, 'pending', 'Takeaway', NULL, '#9866', 12.60, 0.00, 16.80, 29.40, 65, NULL, 65, NULL, NULL, '2025-02-02 08:43:14', '2025-02-02 08:43:14', NULL, NULL, NULL, '2025-02-02', 'INV-17-7329', 10, '10:43:14', NULL, 0.00),
(25, 'pending', 'Takeaway', NULL, '#4771', 12.60, 0.00, 16.80, 29.40, 65, NULL, 65, NULL, NULL, '2025-02-02 09:40:48', '2025-02-02 09:40:48', NULL, NULL, NULL, '2025-02-02', 'INV-18-8223', 10, '11:40:48', NULL, 0.00),
(26, 'pending', 'Takeaway', NULL, '#8837', 12.60, 0.00, 16.80, 29.40, 65, NULL, 65, NULL, NULL, '2025-02-02 09:40:53', '2025-02-02 09:40:53', NULL, NULL, NULL, '2025-02-02', 'INV-19-4966', 10, '11:40:53', NULL, 0.00),
(27, 'pending', 'Takeaway', NULL, '#4757', 12.60, 0.00, 16.80, 29.40, 65, NULL, 65, NULL, NULL, '2025-02-02 09:43:16', '2025-02-02 09:43:16', NULL, NULL, NULL, '2025-02-02', 'INV-20-1246', 10, '11:43:16', NULL, 0.00),
(28, 'pending', 'Delivery', '', '#2113', 3.00, 10.00, 4.00, 117.00, 65, NULL, 65, NULL, NULL, '2025-02-02 12:24:04', '2025-02-02 12:24:04', NULL, NULL, NULL, '2025-02-02', 'INV-21-2803', 10, '14:24:04', 11, 100.00),
(29, 'pending', 'Takeaway', NULL, '#8909', 12.60, 0.00, 16.80, 29.40, 65, NULL, 65, NULL, NULL, '2025-02-02 12:28:14', '2025-02-02 12:28:14', NULL, NULL, NULL, '2025-02-02', 'INV-22-7605', 10, '14:28:14', NULL, 0.00),
(30, 'pending', 'Takeaway', NULL, '#2061', 12.60, 0.00, 16.80, 29.40, 65, NULL, 65, NULL, NULL, '2025-02-02 12:29:37', '2025-02-02 12:29:37', NULL, NULL, NULL, '2025-02-02', 'INV-23-4572', 10, '14:29:37', NULL, 0.00),
(31, 'pending', 'Delivery', '', '#9058', 3.00, 10.00, 4.00, 117.00, 65, NULL, 65, NULL, NULL, '2025-02-02 12:30:19', '2025-02-02 12:30:19', NULL, NULL, NULL, '2025-02-02', 'INV-24-7645', 10, '14:30:19', 11, 100.00),
(32, 'pending', 'Takeaway', NULL, '#2096', 12.60, 0.00, 16.80, 29.40, 65, NULL, 65, NULL, NULL, '2025-02-02 12:30:30', '2025-02-02 12:30:30', NULL, NULL, NULL, '2025-02-02', 'INV-25-1176', 10, '14:30:30', NULL, 0.00),
(33, 'pending', 'Delivery', '', '#3679', 3.00, 10.00, 4.00, 117.00, 65, NULL, 65, NULL, NULL, '2025-02-02 12:52:26', '2025-02-02 12:52:26', NULL, NULL, NULL, '2025-02-02', 'INV-26-1457', 10, '14:52:26', 11, 100.00),
(34, 'pending', 'Takeaway', NULL, '#6421', 12.60, 0.00, 16.80, 29.40, 65, NULL, 65, NULL, NULL, '2025-02-02 13:43:53', '2025-02-02 13:43:53', NULL, NULL, NULL, '2025-02-02', 'INV-27-3169', 10, '15:43:53', NULL, 0.00),
(35, 'pending', 'Delivery', '', '#4033', 21.30, 10.00, 28.40, 159.70, 65, NULL, 65, NULL, NULL, '2025-02-03 14:10:55', '2025-02-03 14:10:55', NULL, NULL, NULL, '2025-02-03', 'INV-28-5139', 10, '16:10:55', 11, 100.00),
(36, 'pending', 'Delivery', '', '#8636', 3.00, 10.00, 4.00, 117.00, 65, NULL, 65, NULL, NULL, '2025-02-03 14:12:34', '2025-02-03 14:12:34', NULL, NULL, NULL, '2025-02-03', 'INV-29-4719', 10, '16:12:34', 11, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_addons`
--

CREATE TABLE `order_addons` (
  `id` bigint UNSIGNED NOT NULL,
  `order_details_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modify_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `price_before_tax` double(8,2) NOT NULL,
  `price_after_tax` double(8,2) NOT NULL,
  `dish_addon_id` bigint UNSIGNED NOT NULL,
  `status` enum('pending','completed','cancel','inprogress') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `tax_value` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_addons`
--

INSERT INTO `order_addons` (`id`, `order_details_id`, `quantity`, `order_id`, `created_by`, `modify_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`, `price_before_tax`, `price_after_tax`, `dish_addon_id`, `status`, `tax_value`) VALUES
(1, 6, 1, 3, 70, NULL, NULL, NULL, '2025-01-22 09:45:52', '2025-01-22 09:45:52', 10.80, 12.00, 34, 'pending', 1.20),
(2, 7, 1, 4, 70, NULL, NULL, NULL, '2025-01-22 09:46:04', '2025-01-22 09:46:04', 10.80, 12.00, 34, 'pending', 1.20),
(3, 12, 1, 5, 33, NULL, NULL, NULL, '2025-01-23 14:49:21', '2025-01-23 14:49:21', 18.00, 20.00, 36, 'pending', 2.00),
(4, 12, 1, 5, 33, NULL, NULL, NULL, '2025-01-23 14:49:21', '2025-01-23 14:49:21', 13.50, 15.00, 37, 'pending', 1.50),
(5, 13, 1, 5, 33, NULL, NULL, NULL, '2025-01-23 14:49:21', '2025-01-23 14:49:21', 18.00, 20.00, 38, 'pending', 2.00),
(6, 13, 1, 5, 33, NULL, NULL, NULL, '2025-01-23 14:49:21', '2025-01-23 14:49:21', 4.50, 5.00, 39, 'pending', 0.50),
(7, 14, 1, 5, 33, NULL, NULL, NULL, '2025-01-23 14:49:21', '2025-01-23 14:49:21', 18.00, 20.00, 38, 'pending', 2.00),
(8, 14, 1, 5, 33, NULL, NULL, NULL, '2025-01-23 14:49:21', '2025-01-23 14:49:21', 4.50, 5.00, 39, 'pending', 0.50),
(9, 43, 1, 20, 65, NULL, NULL, NULL, '2025-02-02 08:24:18', '2025-02-02 08:24:18', 7.00, 10.00, 34, 'pending', 3.00),
(10, 45, 1, 21, 65, NULL, NULL, NULL, '2025-02-02 08:26:39', '2025-02-02 08:26:39', 7.00, 10.00, 34, 'pending', 3.00),
(11, 47, 1, 22, 65, NULL, NULL, NULL, '2025-02-02 08:40:56', '2025-02-02 08:40:56', 7.00, 10.00, 34, 'pending', 3.00),
(12, 49, 1, 23, 65, NULL, NULL, NULL, '2025-02-02 08:42:33', '2025-02-02 08:42:33', 7.00, 10.00, 34, 'pending', 3.00),
(13, 51, 1, 24, 65, NULL, NULL, NULL, '2025-02-02 08:43:14', '2025-02-02 08:43:14', 7.00, 10.00, 34, 'pending', 3.00),
(14, 53, 1, 25, 65, NULL, NULL, NULL, '2025-02-02 09:40:48', '2025-02-02 09:40:48', 7.00, 10.00, 34, 'pending', 3.00),
(15, 55, 1, 26, 65, NULL, NULL, NULL, '2025-02-02 09:40:53', '2025-02-02 09:40:53', 7.00, 10.00, 34, 'pending', 3.00),
(16, 57, 1, 27, 65, NULL, NULL, NULL, '2025-02-02 09:43:16', '2025-02-02 09:43:16', 7.00, 10.00, 34, 'pending', 3.00),
(17, 60, 1, 29, 65, NULL, NULL, NULL, '2025-02-02 12:28:14', '2025-02-02 12:28:14', 7.00, 10.00, 34, 'pending', 3.00),
(18, 62, 1, 30, 65, NULL, NULL, NULL, '2025-02-02 12:29:37', '2025-02-02 12:29:37', 7.00, 10.00, 34, 'pending', 3.00),
(19, 65, 1, 32, 65, NULL, NULL, NULL, '2025-02-02 12:30:30', '2025-02-02 12:30:30', 7.00, 10.00, 34, 'pending', 3.00),
(20, 68, 1, 34, 65, NULL, NULL, NULL, '2025-02-02 13:43:53', '2025-02-02 13:43:53', 7.00, 10.00, 34, 'pending', 3.00),
(21, 70, 1, 35, 65, NULL, NULL, NULL, '2025-02-03 14:10:55', '2025-02-03 14:10:55', 7.70, 11.00, 35, 'pending', 3.30);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` bigint UNSIGNED NOT NULL,
  `status` enum('pending','completed','cancel','inprogress') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `quantity` int DEFAULT NULL,
  `total` double(8,2) NOT NULL,
  `price_befor_tax` double(8,2) NOT NULL,
  `price_after_tax` double(8,2) NOT NULL,
  `tax_value` double(8,2) DEFAULT NULL,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modify_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `dish_id` bigint UNSIGNED DEFAULT NULL,
  `dish_size_id` bigint UNSIGNED DEFAULT NULL,
  `offer_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `status`, `quantity`, `total`, `price_befor_tax`, `price_after_tax`, `tax_value`, `note`, `order_id`, `created_by`, `modify_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `dish_id`, `dish_size_id`, `offer_id`) VALUES
(1, 'pending', 1, 30.00, 25.50, 30.00, 4.50, NULL, 1, 33, NULL, NULL, '2025-01-21 11:53:30', '2025-01-21 11:53:30', NULL, 48, NULL, NULL),
(2, 'pending', 2, 70.00, 119.00, 140.00, 21.00, NULL, 1, 33, NULL, NULL, '2025-01-21 11:53:30', '2025-01-21 11:53:30', NULL, 55, NULL, NULL),
(3, 'pending', 4, 70.00, 238.00, 280.00, 42.00, NULL, 1, 33, NULL, NULL, '2025-01-21 11:53:30', '2025-01-21 11:53:30', NULL, 56, NULL, NULL),
(4, 'pending', 20, 60.00, 51.00, 60.00, 9.00, NULL, 1, 33, NULL, NULL, '2025-01-21 11:53:30', '2025-01-21 11:53:30', NULL, 183, NULL, NULL),
(5, 'pending', 2, 50.00, 90.00, 100.00, 10.00, NULL, 2, 33, NULL, NULL, '2025-01-22 08:07:27', '2025-01-22 08:07:27', NULL, 57, NULL, NULL),
(6, 'pending', 1, 12.00, 10.80, 12.00, 1.20, NULL, 3, 70, NULL, NULL, '2025-01-22 09:45:52', '2025-01-22 09:45:52', NULL, 52, NULL, 22),
(7, 'pending', 1, 12.00, 10.80, 12.00, 1.20, NULL, 4, 70, NULL, NULL, '2025-01-22 09:46:04', '2025-01-22 09:46:04', NULL, 52, NULL, NULL),
(8, 'pending', 1, 50.00, 45.00, 50.00, 5.00, NULL, 5, 33, NULL, NULL, '2025-01-23 14:49:21', '2025-01-23 14:49:21', NULL, 48, NULL, NULL),
(9, 'pending', 1, 50.00, 45.00, 50.00, 5.00, NULL, 5, 33, NULL, NULL, '2025-01-23 14:49:21', '2025-01-23 14:49:21', NULL, 54, NULL, NULL),
(10, 'pending', 1, 70.00, 63.00, 70.00, 7.00, NULL, 5, 33, NULL, NULL, '2025-01-23 14:49:21', '2025-01-23 14:49:21', NULL, 55, NULL, NULL),
(11, 'pending', 1, 60.00, 54.00, 60.00, 6.00, NULL, 5, 33, NULL, NULL, '2025-01-23 14:49:21', '2025-01-23 14:49:21', NULL, 55, NULL, NULL),
(12, 'pending', 1, 70.00, 63.00, 70.00, 7.00, NULL, 5, 33, NULL, NULL, '2025-01-23 14:49:21', '2025-01-23 14:49:21', NULL, 56, NULL, NULL),
(13, 'pending', 1, 70.00, 63.00, 70.00, 7.00, NULL, 5, 33, NULL, NULL, '2025-01-23 14:49:21', '2025-01-23 14:49:21', NULL, 56, NULL, NULL),
(14, 'pending', 1, 60.00, 54.00, 60.00, 6.00, NULL, 5, 33, NULL, NULL, '2025-01-23 14:49:21', '2025-01-23 14:49:21', NULL, 56, NULL, NULL),
(15, 'pending', 3, 60.00, 162.00, 180.00, 18.00, NULL, 6, 33, NULL, NULL, '2025-01-27 09:21:33', '2025-01-27 09:21:33', NULL, 56, NULL, NULL),
(16, 'pending', 2, 50.00, 90.00, 100.00, 10.00, NULL, 7, 33, NULL, NULL, '2025-01-27 09:25:01', '2025-01-27 09:25:01', NULL, 57, NULL, NULL),
(17, 'pending', 1, 60.00, 54.00, 60.00, 6.00, NULL, 8, 82, NULL, NULL, '2025-01-27 11:14:25', '2025-01-27 11:14:25', NULL, 56, NULL, NULL),
(18, 'pending', 1, 50.00, 45.00, 50.00, 5.00, NULL, 9, 87, NULL, NULL, '2025-01-27 14:20:49', '2025-01-27 14:20:49', NULL, 48, NULL, 23),
(19, 'pending', 1, 30.00, 21.00, 30.00, 9.00, NULL, 10, 33, NULL, NULL, '2025-01-29 11:09:49', '2025-01-29 11:09:49', NULL, 48, NULL, NULL),
(20, 'pending', 1, 30.00, 21.00, 30.00, 9.00, NULL, 10, 33, NULL, NULL, '2025-01-29 11:09:50', '2025-01-29 11:09:50', NULL, 48, NULL, NULL),
(21, 'pending', 1, 50.00, 35.00, 50.00, 15.00, NULL, 10, 33, NULL, NULL, '2025-01-29 11:09:50', '2025-01-29 11:09:50', NULL, 54, NULL, NULL),
(22, 'pending', 1, 50.00, 35.00, 50.00, 15.00, NULL, 10, 33, NULL, NULL, '2025-01-29 11:09:50', '2025-01-29 11:09:50', NULL, 54, NULL, NULL),
(23, 'pending', 1, 60.00, 42.00, 60.00, 18.00, NULL, 10, 33, NULL, NULL, '2025-01-29 11:09:50', '2025-01-29 11:09:50', NULL, 55, NULL, NULL),
(24, 'pending', 1, 70.00, 49.00, 70.00, 21.00, NULL, 10, 33, NULL, NULL, '2025-01-29 11:09:50', '2025-01-29 11:09:50', NULL, 55, NULL, NULL),
(25, 'pending', 1, 20.00, 14.00, 20.00, 6.00, NULL, 10, 33, NULL, NULL, '2025-01-29 11:09:50', '2025-01-29 11:09:50', NULL, 52, NULL, NULL),
(26, 'pending', 1, 12.00, 8.40, 12.00, 3.60, NULL, 10, 33, NULL, NULL, '2025-01-29 11:09:50', '2025-01-29 11:09:50', NULL, 52, NULL, NULL),
(27, 'pending', 1, 30.00, 21.00, 30.00, 9.00, NULL, 11, 33, NULL, NULL, '2025-01-29 11:27:31', '2025-01-29 11:27:31', NULL, 48, NULL, NULL),
(28, 'pending', 1, 30.00, 21.00, 30.00, 9.00, NULL, 11, 33, NULL, NULL, '2025-01-29 11:27:31', '2025-01-29 11:27:31', NULL, 48, NULL, NULL),
(29, 'pending', 1, 50.00, 35.00, 50.00, 15.00, NULL, 11, 33, NULL, NULL, '2025-01-29 11:27:31', '2025-01-29 11:27:31', NULL, 54, NULL, NULL),
(30, 'pending', 1, 50.00, 35.00, 50.00, 15.00, NULL, 11, 33, NULL, NULL, '2025-01-29 11:27:31', '2025-01-29 11:27:31', NULL, 54, NULL, NULL),
(31, 'pending', 1, 60.00, 42.00, 60.00, 18.00, NULL, 11, 33, NULL, NULL, '2025-01-29 11:27:31', '2025-01-29 11:27:31', NULL, 55, NULL, NULL),
(32, 'pending', 1, 20.00, 14.00, 20.00, 6.00, NULL, 11, 33, NULL, NULL, '2025-01-29 11:27:31', '2025-01-29 11:27:31', NULL, 52, NULL, NULL),
(33, 'pending', 1, 12.00, 8.40, 12.00, 3.60, NULL, 11, 33, NULL, NULL, '2025-01-29 11:27:31', '2025-01-29 11:27:31', NULL, 52, NULL, NULL),
(34, 'pending', 1, 60.00, 42.00, 60.00, 18.00, NULL, 12, 33, NULL, NULL, '2025-01-29 12:51:04', '2025-01-29 12:51:04', NULL, 55, NULL, NULL),
(35, 'pending', 1, 60.00, 42.00, 60.00, 18.00, NULL, 12, 33, NULL, NULL, '2025-01-29 12:51:04', '2025-01-29 12:51:04', NULL, 56, NULL, NULL),
(42, 'pending', 4, 5.00, 14.00, 20.00, 6.00, NULL, 20, 65, NULL, NULL, '2025-02-02 08:24:18', '2025-02-02 08:24:18', NULL, 185, NULL, NULL),
(43, 'pending', 1, 12.00, 8.40, 12.00, 3.60, NULL, 20, 65, NULL, NULL, '2025-02-02 08:24:18', '2025-02-02 08:24:18', NULL, 184, 35, NULL),
(44, 'pending', 4, 5.00, 14.00, 20.00, 6.00, NULL, 21, 65, NULL, NULL, '2025-02-02 08:26:39', '2025-02-02 08:26:39', NULL, 185, NULL, NULL),
(45, 'pending', 1, 12.00, 8.40, 12.00, 3.60, NULL, 21, 65, NULL, NULL, '2025-02-02 08:26:39', '2025-02-02 08:26:39', NULL, 184, 35, NULL),
(46, 'pending', 4, 5.00, 14.00, 20.00, 6.00, NULL, 22, 65, NULL, NULL, '2025-02-02 08:40:56', '2025-02-02 08:40:56', NULL, 185, NULL, NULL),
(47, 'pending', 1, 12.00, 8.40, 12.00, 3.60, NULL, 22, 65, NULL, NULL, '2025-02-02 08:40:56', '2025-02-02 08:40:56', NULL, 184, 35, NULL),
(48, 'pending', 4, 5.00, 14.00, 20.00, 6.00, NULL, 23, 65, NULL, NULL, '2025-02-02 08:42:33', '2025-02-02 08:42:33', NULL, 185, NULL, NULL),
(49, 'pending', 1, 12.00, 8.40, 12.00, 3.60, NULL, 23, 65, NULL, NULL, '2025-02-02 08:42:33', '2025-02-02 08:42:33', NULL, 184, 35, NULL),
(50, 'pending', 4, 5.00, 14.00, 20.00, 6.00, NULL, 24, 65, NULL, NULL, '2025-02-02 08:43:14', '2025-02-02 08:43:14', NULL, 185, NULL, NULL),
(51, 'pending', 1, 12.00, 8.40, 12.00, 3.60, NULL, 24, 65, NULL, NULL, '2025-02-02 08:43:14', '2025-02-02 08:43:14', NULL, 184, 35, NULL),
(52, 'pending', 4, 5.00, 14.00, 20.00, 6.00, NULL, 25, 65, NULL, NULL, '2025-02-02 09:40:48', '2025-02-02 09:40:48', NULL, 185, NULL, NULL),
(53, 'pending', 1, 12.00, 8.40, 12.00, 3.60, NULL, 25, 65, NULL, NULL, '2025-02-02 09:40:48', '2025-02-02 09:40:48', NULL, 184, 35, NULL),
(54, 'pending', 4, 5.00, 14.00, 20.00, 6.00, NULL, 26, 65, NULL, NULL, '2025-02-02 09:40:53', '2025-02-02 09:40:53', NULL, 185, NULL, NULL),
(55, 'pending', 1, 12.00, 8.40, 12.00, 3.60, NULL, 26, 65, NULL, NULL, '2025-02-02 09:40:53', '2025-02-02 09:40:53', NULL, 184, 35, NULL),
(56, 'pending', 4, 5.00, 14.00, 20.00, 6.00, NULL, 27, 65, NULL, NULL, '2025-02-02 09:43:16', '2025-02-02 09:43:16', NULL, 185, NULL, NULL),
(57, 'pending', 1, 12.00, 8.40, 12.00, 3.60, NULL, 27, 65, NULL, NULL, '2025-02-02 09:43:16', '2025-02-02 09:43:16', NULL, 184, 35, NULL),
(58, 'pending', 1, 10.00, 7.00, 10.00, 3.00, NULL, 28, 65, NULL, NULL, '2025-02-02 12:24:04', '2025-02-02 12:24:04', NULL, 46, NULL, NULL),
(59, 'pending', 4, 5.00, 14.00, 20.00, 6.00, NULL, 29, 65, NULL, NULL, '2025-02-02 12:28:14', '2025-02-02 12:28:14', NULL, 185, NULL, NULL),
(60, 'pending', 1, 12.00, 8.40, 12.00, 3.60, NULL, 29, 65, NULL, NULL, '2025-02-02 12:28:14', '2025-02-02 12:28:14', NULL, 184, 35, NULL),
(61, 'pending', 4, 5.00, 14.00, 20.00, 6.00, NULL, 30, 65, NULL, NULL, '2025-02-02 12:29:37', '2025-02-02 12:29:37', NULL, 185, NULL, NULL),
(62, 'pending', 1, 12.00, 8.40, 12.00, 3.60, NULL, 30, 65, NULL, NULL, '2025-02-02 12:29:37', '2025-02-02 12:29:37', NULL, 184, 35, NULL),
(63, 'pending', 1, 10.00, 7.00, 10.00, 3.00, NULL, 31, 65, NULL, NULL, '2025-02-02 12:30:19', '2025-02-02 12:30:19', NULL, 46, NULL, NULL),
(64, 'pending', 4, 5.00, 14.00, 20.00, 6.00, NULL, 32, 65, NULL, NULL, '2025-02-02 12:30:30', '2025-02-02 12:30:30', NULL, 185, NULL, NULL),
(65, 'pending', 1, 12.00, 8.40, 12.00, 3.60, NULL, 32, 65, NULL, NULL, '2025-02-02 12:30:30', '2025-02-02 12:30:30', NULL, 184, 35, NULL),
(66, 'pending', 1, 10.00, 7.00, 10.00, 3.00, NULL, 33, 65, NULL, NULL, '2025-02-02 12:52:26', '2025-02-02 12:52:26', NULL, 46, NULL, NULL),
(67, 'pending', 4, 5.00, 14.00, 20.00, 6.00, NULL, 34, 65, NULL, NULL, '2025-02-02 13:43:53', '2025-02-02 13:43:53', NULL, 185, NULL, NULL),
(68, 'pending', 1, 12.00, 8.40, 12.00, 3.60, NULL, 34, 65, NULL, NULL, '2025-02-02 13:43:53', '2025-02-02 13:43:53', NULL, 184, 35, NULL),
(69, 'pending', 1, 10.00, 7.00, 10.00, 3.00, NULL, 35, 65, NULL, NULL, '2025-02-03 14:10:55', '2025-02-03 14:10:55', NULL, 46, NULL, NULL),
(70, 'pending', 1, 50.00, 35.00, 50.00, 15.00, NULL, 35, 65, NULL, NULL, '2025-02-03 14:10:55', '2025-02-03 14:10:55', NULL, 54, NULL, NULL),
(71, 'pending', 1, 10.00, 7.00, 10.00, 3.00, NULL, 36, 65, NULL, NULL, '2025-02-03 14:12:34', '2025-02-03 14:12:34', NULL, 46, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_products`
--

CREATE TABLE `order_products` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `product_unit_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_refunds`
--

CREATE TABLE `order_refunds` (
  `id` bigint UNSIGNED NOT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modify_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `date` date NOT NULL,
  `status` enum('accepted','pending','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `invoice_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `item_type` enum('product','addon','dish') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_trackings`
--

CREATE TABLE `order_trackings` (
  `id` bigint UNSIGNED NOT NULL,
  `order_status` enum('pending','in_progress','completed','on_way','deliverd','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `order_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modify_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_trackings`
--

INSERT INTO `order_trackings` (`id`, `order_status`, `order_id`, `created_by`, `modify_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `time`) VALUES
(1, 'pending', 1, 33, NULL, NULL, '2025-01-21 11:53:30', '2025-01-21 11:53:30', NULL, NULL),
(2, 'pending', 2, 33, NULL, NULL, '2025-01-22 08:07:27', '2025-01-22 08:07:27', NULL, NULL),
(3, 'pending', 3, 70, NULL, NULL, '2025-01-22 09:45:52', '2025-01-22 09:45:52', NULL, NULL),
(4, 'pending', 4, 70, NULL, NULL, '2025-01-22 09:46:04', '2025-01-22 09:46:04', NULL, NULL),
(5, 'cancelled', 5, 33, NULL, NULL, '2025-01-23 14:49:21', '2025-01-26 07:57:36', NULL, NULL),
(6, 'pending', 6, 33, NULL, NULL, '2025-01-27 09:21:33', '2025-01-27 09:21:33', NULL, NULL),
(7, 'pending', 7, 33, NULL, NULL, '2025-01-27 09:25:01', '2025-01-27 09:25:01', NULL, NULL),
(8, 'pending', 8, 82, NULL, NULL, '2025-01-27 11:14:25', '2025-01-27 11:14:25', NULL, NULL),
(9, 'pending', 9, 87, NULL, NULL, '2025-01-27 14:20:49', '2025-01-27 14:20:49', NULL, NULL),
(10, 'pending', 10, 33, NULL, NULL, '2025-01-29 11:09:50', '2025-01-29 11:09:50', NULL, NULL),
(11, 'cancelled', 11, 33, NULL, NULL, '2025-01-29 11:27:31', '2025-01-29 11:27:38', NULL, NULL),
(12, 'pending', 12, 33, NULL, NULL, '2025-01-29 12:51:04', '2025-01-29 12:51:04', NULL, NULL),
(13, 'pending', 20, 65, NULL, NULL, '2025-02-02 08:24:18', '2025-02-02 08:24:18', NULL, NULL),
(14, 'pending', 21, 65, NULL, NULL, '2025-02-02 08:26:39', '2025-02-02 08:26:39', NULL, NULL),
(15, 'pending', 22, 65, NULL, NULL, '2025-02-02 08:40:56', '2025-02-02 08:40:56', NULL, NULL),
(16, 'pending', 23, 65, NULL, NULL, '2025-02-02 08:42:33', '2025-02-02 08:42:33', NULL, NULL),
(17, 'pending', 24, 65, NULL, NULL, '2025-02-02 08:43:14', '2025-02-02 08:43:14', NULL, NULL),
(18, 'pending', 25, 65, NULL, NULL, '2025-02-02 09:40:48', '2025-02-02 09:40:48', NULL, NULL),
(19, 'pending', 26, 65, NULL, NULL, '2025-02-02 09:40:53', '2025-02-02 09:40:53', NULL, NULL),
(20, 'pending', 27, 65, NULL, NULL, '2025-02-02 09:43:16', '2025-02-02 09:43:16', NULL, NULL),
(21, 'pending', 28, 65, NULL, NULL, '2025-02-02 12:24:04', '2025-02-02 12:24:04', NULL, NULL),
(22, 'pending', 29, 65, NULL, NULL, '2025-02-02 12:28:14', '2025-02-02 12:28:14', NULL, NULL),
(23, 'pending', 30, 65, NULL, NULL, '2025-02-02 12:29:37', '2025-02-02 12:29:37', NULL, NULL),
(24, 'pending', 31, 65, NULL, NULL, '2025-02-02 12:30:19', '2025-02-02 12:30:19', NULL, NULL),
(25, 'pending', 32, 65, NULL, NULL, '2025-02-02 12:30:30', '2025-02-02 12:30:30', NULL, NULL),
(26, 'pending', 33, 65, NULL, NULL, '2025-02-02 12:52:26', '2025-02-02 12:52:26', NULL, NULL),
(27, 'pending', 34, 65, NULL, NULL, '2025-02-02 13:43:53', '2025-02-02 13:43:53', NULL, NULL),
(28, 'pending', 35, 65, NULL, NULL, '2025-02-03 14:10:55', '2025-02-03 14:10:55', NULL, NULL),
(29, 'pending', 36, 65, NULL, NULL, '2025-02-03 14:12:34', '2025-02-03 14:12:34', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_transactions`
--

CREATE TABLE `order_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `payment_status` enum('paid','unpaid','payment_failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `payment_method` enum('cash','credit_card','online') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `paid` double(8,2) NOT NULL,
  `date` date NOT NULL,
  `refund` double(8,2) DEFAULT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modify_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `discount_id` bigint UNSIGNED DEFAULT NULL,
  `coupon_id` bigint UNSIGNED DEFAULT NULL,
  `points_num` int NOT NULL DEFAULT '0',
  `is_refund` tinyint(1) NOT NULL DEFAULT '0',
  `payment_gateway_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_gateway_date` date DEFAULT NULL,
  `payment_gateway_currency` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_gateway_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_gateway_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_transactions`
--

INSERT INTO `order_transactions` (`id`, `payment_status`, `payment_method`, `transaction_id`, `paid`, `date`, `refund`, `order_id`, `created_by`, `modify_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `discount_id`, `coupon_id`, `points_num`, `is_refund`, `payment_gateway_reference`, `payment_gateway_date`, `payment_gateway_currency`, `payment_gateway_status`, `payment_gateway_method`, `reason`) VALUES
(1, 'unpaid', 'cash', '2183ff11-047f-44fb-9e68-d93893719564', 407.00, '2025-01-21', NULL, 1, 33, NULL, NULL, '2025-01-21 11:53:30', '2025-01-21 11:53:30', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'unpaid', 'cash', 'f2f364b5-d713-4928-a00d-09e3fac17ac5', 135.00, '2025-01-22', NULL, 2, 33, NULL, NULL, '2025-01-22 08:07:27', '2025-01-22 08:07:27', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'unpaid', 'credit_card', 'd1510889-908b-46a0-8bba-e7075bc850a6', 74.20, '2025-01-22', NULL, 3, 70, NULL, NULL, '2025-01-22 09:45:52', '2025-01-22 09:45:52', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'unpaid', 'credit_card', 'aa08dba7-0cf4-4927-862f-7972719ec477', 74.20, '2025-01-22', NULL, 4, 70, NULL, NULL, '2025-01-22 09:46:04', '2025-01-22 09:46:04', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'unpaid', 'cash', 'fa7abd41-c91b-472d-b9de-7366bab80319', 467.00, '2025-01-23', NULL, 5, 33, NULL, NULL, '2025-01-23 14:49:21', '2025-01-23 14:49:21', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'unpaid', 'cash', '415118e6-a79a-4994-950b-93a2b6ab7f29', 199.00, '2025-01-27', NULL, 6, 33, NULL, NULL, '2025-01-27 09:21:33', '2025-01-27 09:21:33', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'unpaid', 'cash', 'd75dcf6a-6df2-4b75-b08d-81affcc22315', 135.00, '2025-01-27', NULL, 7, 33, NULL, NULL, '2025-01-27 09:25:01', '2025-01-27 09:25:01', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'unpaid', 'cash', 'e193f194-d074-4b35-b27b-831478154d97', 103.00, '2025-01-27', NULL, 8, 82, NULL, NULL, '2025-01-27 11:14:26', '2025-01-27 11:14:26', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'unpaid', 'cash', '7066d6a4-41f7-4a45-a5b5-9fa41e4a7477', 95.00, '2025-01-27', NULL, 9, 87, NULL, NULL, '2025-01-27 14:20:49', '2025-01-27 14:20:49', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'unpaid', 'cash', '0c4c3c00-8227-4ddc-a4f8-2286f07d2f57', 238.80, '2025-01-29', NULL, 10, 33, NULL, NULL, '2025-01-29 11:09:50', '2025-01-29 11:09:50', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'unpaid', 'cash', 'da31d9e1-ff91-4bcf-99f5-980cd07526ec', 210.80, '2025-01-29', NULL, 11, 33, NULL, NULL, '2025-01-29 11:27:31', '2025-01-29 11:27:31', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'unpaid', 'cash', 'd6e56aaa-7b02-40a5-aa7b-0df3466378c4', 158.00, '2025-01-29', NULL, 12, 33, NULL, NULL, '2025-01-29 12:51:04', '2025-01-29 12:51:04', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'unpaid', 'cash', 'f9755e20-bf23-4a6b-ad51-834b5b46ac55', 16.80, '2025-02-02', NULL, 20, 65, NULL, NULL, '2025-02-02 08:24:19', '2025-02-02 08:24:19', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'unpaid', 'cash', '9171428f-8788-4545-b381-b0938c786927', 16.80, '2025-02-02', NULL, 21, 65, NULL, NULL, '2025-02-02 08:26:39', '2025-02-02 08:26:39', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'unpaid', 'cash', '11aead79-81fa-4265-944d-41e054826ee3', 16.80, '2025-02-02', NULL, 22, 65, NULL, NULL, '2025-02-02 08:40:56', '2025-02-02 08:40:56', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'unpaid', 'cash', '7539ad27-f4b1-4582-8446-2e4f22b9f86c', 16.80, '2025-02-02', NULL, 23, 65, NULL, NULL, '2025-02-02 08:42:33', '2025-02-02 08:42:33', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'unpaid', 'cash', '9076d137-6d34-4e74-a019-67faf4a5810f', 16.80, '2025-02-02', NULL, 24, 65, NULL, NULL, '2025-02-02 08:43:14', '2025-02-02 08:43:14', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'unpaid', 'cash', 'dd239301-4a50-4a28-9669-5934b3e662ac', 16.80, '2025-02-02', NULL, 25, 65, NULL, NULL, '2025-02-02 09:40:48', '2025-02-02 09:40:48', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'unpaid', 'cash', '57b8c074-b02c-4ae1-8a71-eea05377ef76', 16.80, '2025-02-02', NULL, 26, 65, NULL, NULL, '2025-02-02 09:40:53', '2025-02-02 09:40:53', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'unpaid', 'cash', 'e4f91a18-ec71-4cac-8122-b419a0d70c84', 16.80, '2025-02-02', NULL, 27, 65, NULL, NULL, '2025-02-02 09:43:16', '2025-02-02 09:43:16', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'unpaid', 'cash', '0f9227be-c857-42ae-bd2d-b3189504246e', 114.00, '2025-02-02', NULL, 28, 65, NULL, NULL, '2025-02-02 12:24:04', '2025-02-02 12:24:04', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'unpaid', 'cash', 'f3431db6-a8fb-48b4-9d96-ae551e3b416a', 16.80, '2025-02-02', NULL, 29, 65, NULL, NULL, '2025-02-02 12:28:14', '2025-02-02 12:28:14', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 'unpaid', 'cash', 'ea88aef4-410e-480c-81e2-a0989ab26b8d', 16.80, '2025-02-02', NULL, 30, 65, NULL, NULL, '2025-02-02 12:29:37', '2025-02-02 12:29:37', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 'unpaid', 'cash', '15a48e29-7297-40f3-bd32-e7f14023ac99', 114.00, '2025-02-02', NULL, 31, 65, NULL, NULL, '2025-02-02 12:30:19', '2025-02-02 12:30:19', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 'unpaid', 'cash', 'b8e23575-5c76-4c39-888b-da80668863c4', 16.80, '2025-02-02', NULL, 32, 65, NULL, NULL, '2025-02-02 12:30:30', '2025-02-02 12:30:30', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 'unpaid', 'cash', 'af322c8b-bee0-496d-90a2-672f028b8004', 114.00, '2025-02-02', NULL, 33, 65, NULL, NULL, '2025-02-02 12:52:26', '2025-02-02 12:52:26', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 'unpaid', 'cash', '00e42346-7cbf-4048-80a0-27839d5e9c2b', 16.80, '2025-02-02', NULL, 34, 65, NULL, NULL, '2025-02-02 13:43:53', '2025-02-02 13:43:53', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 'unpaid', 'cash', '823381d2-fd12-46d2-a3b2-9524ad7ee9ad', 138.40, '2025-02-03', NULL, 35, 65, NULL, NULL, '2025-02-03 14:10:56', '2025-02-03 14:10:56', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 'unpaid', 'cash', '070d5673-a374-4e68-b7cf-8e03f688b864', 114.00, '2025-02-03', NULL, 36, 65, NULL, NULL, '2025-02-03 14:12:34', '2025-02-03 14:12:34', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` bigint UNSIGNED NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `overtime_settings`
--

CREATE TABLE `overtime_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `overtime_type_id` bigint UNSIGNED NOT NULL,
  `quentity` double(8,2) DEFAULT NULL,
  `percent` double(8,2) DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `overtime_types`
--

CREATE TABLE `overtime_types` (
  `id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `code`, `description`, `created_at`, `updated_at`) VALUES
(1, 'C', 'Cash', '2025-01-29 14:10:32', '2025-01-29 14:10:32'),
(2, 'V', 'Visa', '2025-01-29 14:10:32', '2025-01-29 14:10:32'),
(3, 'CC', 'Cash with contractor', '2025-01-29 14:10:32', '2025-01-29 14:10:32'),
(4, 'VC', 'Visa with contractor', '2025-01-29 14:10:32', '2025-01-29 14:10:32'),
(5, 'VO', 'Vouchers', '2025-01-29 14:10:32', '2025-01-29 14:10:32'),
(6, 'PR', 'Promotion', '2025-01-29 14:10:32', '2025-01-29 14:10:32'),
(7, 'GC', 'Gift Card', '2025-01-29 14:10:32', '2025-01-29 14:10:32'),
(8, 'P', 'Points', '2025-01-29 14:10:32', '2025-01-29 14:10:32'),
(9, 'O', 'Others', '2025-01-29 14:10:32', '2025-01-29 14:10:32');

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

CREATE TABLE `payrolls` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `base_salary` decimal(10,2) NOT NULL,
  `bonus` decimal(10,2) DEFAULT NULL,
  `deductions` decimal(10,2) DEFAULT NULL,
  `taxes` decimal(10,2) DEFAULT NULL,
  `insurance` decimal(10,2) DEFAULT NULL,
  `advance` decimal(10,2) DEFAULT NULL,
  `net_salary` decimal(10,2) NOT NULL,
  `pay_date` date NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penalties`
--

CREATE TABLE `penalties` (
  `id` bigint UNSIGNED NOT NULL,
  `reason_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penalty_deductions`
--

CREATE TABLE `penalty_deductions` (
  `id` bigint UNSIGNED NOT NULL,
  `penalty_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `deduction_amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penalty_reasons`
--

CREATE TABLE `penalty_reasons` (
  `id` bigint UNSIGNED NOT NULL,
  `reason_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `punishment_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `punishment_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'view dashboard', 'admin', '2024-12-16 12:06:15', '2024-12-22 08:05:20', 0),
(5, 'view actionbacklogs', 'admin', '2024-12-16 12:06:15', '2025-01-20 14:55:41', 0),
(6, 'create actionbacklogs', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:52:23', 1),
(7, 'update actionbacklogs', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:52:36', 1),
(8, 'delete actionbacklogs', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:52:45', 1),
(9, 'view advances', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:52:58', 1),
(10, 'create advances', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:54:38', 1),
(11, 'update advances', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:54:26', 1),
(12, 'delete advances', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:54:16', 1),
(13, 'view advance_requests', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:54:04', 1),
(14, 'create advance_requests', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:55:03', 1),
(15, 'update advance_requests', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:55:39', 1),
(16, 'delete advance_requests', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:55:58', 1),
(17, 'view advance_settings', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:56:27', 1),
(18, 'create advance_settings', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:57:00', 1),
(19, 'update advance_settings', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:57:34', 1),
(20, 'delete advance_settings', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:58:00', 1),
(21, 'view branches', 'admin', '2024-12-16 12:06:15', '2024-12-16 12:06:15', 0),
(22, 'create branches', 'admin', '2024-12-16 12:06:15', '2024-12-16 12:06:15', 0),
(23, 'update branches', 'admin', '2024-12-16 12:06:15', '2024-12-16 12:06:15', 0),
(24, 'delete branches', 'admin', '2024-12-16 12:06:15', '2024-12-16 12:06:15', 0),
(25, 'view branch_coupon', 'admin', '2024-12-16 12:06:15', '2024-12-16 12:06:15', 0),
(26, 'create branch_coupon', 'admin', '2024-12-16 12:06:15', '2024-12-26 06:58:35', 0),
(27, 'update branch_coupon', 'admin', '2024-12-16 12:06:15', '2024-12-16 12:06:15', 0),
(28, 'delete branch_coupon', 'admin', '2024-12-16 12:06:15', '2024-12-16 12:06:15', 0),
(29, 'view branch_discount', 'admin', '2024-12-16 12:06:15', '2024-12-16 12:06:15', 0),
(30, 'create branch_discount', 'admin', '2024-12-16 12:06:15', '2024-12-16 12:06:15', 0),
(31, 'update branch_discount', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(32, 'delete branch_discount', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(33, 'view branch_recipe', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(34, 'create branch_recipe', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(35, 'update branch_recipe', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(36, 'delete branch_recipe', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(37, 'view brands', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(38, 'create brands', 'admin', '2024-12-16 12:06:16', '2024-12-26 06:59:19', 0),
(39, 'update brands', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(40, 'delete brands', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(41, 'view cashier_machines', 'admin', '2024-12-16 12:06:16', '2024-12-26 06:59:48', 1),
(42, 'create cashier_machines', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:00:00', 1),
(43, 'update cashier_machines', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:00:10', 1),
(44, 'delete cashier_machines', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:00:22', 1),
(45, 'view cashier_machine_logs', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:00:42', 1),
(46, 'create cashier_machine_logs', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:01:11', 1),
(47, 'update cashier_machine_logs', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:01:32', 1),
(48, 'delete cashier_machine_logs', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:01:44', 1),
(49, 'view categories', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(50, 'create categories', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:02:06', 0),
(51, 'update categories', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(52, 'delete categories', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(53, 'view client_addresses', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(54, 'create client_addresses', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:02:27', 0),
(55, 'update client_addresses', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(56, 'delete client_addresses', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(57, 'view client_details', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(58, 'create client_details', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(59, 'update client_details', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(60, 'delete client_details', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(61, 'view colors', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(62, 'create colors', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(63, 'update colors', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(64, 'delete colors', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(65, 'view countries', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(66, 'create countries', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:03:17', 0),
(67, 'update countries', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(68, 'delete countries', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(69, 'view coupons', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(70, 'create coupons', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:03:04', 0),
(71, 'update coupons', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(72, 'delete coupons', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(73, 'view cuisines', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(74, 'create cuisines', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:04:43', 0),
(75, 'update cuisines', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:04:58', 0),
(76, 'delete cuisines', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:05:15', 0),
(77, 'view delays', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:05:29', 1),
(78, 'create delays', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:05:40', 1),
(79, 'update delays', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:05:52', 1),
(80, 'delete delays', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:06:04', 1),
(81, 'view delay_deductions', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:06:42', 1),
(82, 'create delay_deductions', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:07:05', 1),
(83, 'update delay_deductions', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:09:45', 1),
(84, 'delete delay_deductions', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:10:14', 1),
(85, 'view delay_times', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:10:44', 1),
(86, 'create delay_times', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:11:16', 1),
(87, 'update delay_times', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:11:44', 1),
(88, 'delete delay_times', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:12:02', 1),
(89, 'view delivery_settings', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:14:22', 1),
(90, 'create delivery_settings', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:14:38', 1),
(91, 'update delivery_settings', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:15:11', 1),
(92, 'delete delivery_settings', 'admin', '2024-12-16 12:06:16', '2024-12-26 07:15:31', 1),
(93, 'view departments', 'admin', '2024-12-16 12:06:16', '2025-01-20 10:16:34', 0),
(94, 'create departments', 'admin', '2024-12-16 12:06:16', '2024-12-16 12:06:16', 0),
(95, 'update departments', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(96, 'delete departments', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(97, 'view discounts', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(98, 'create discounts', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(99, 'update discounts', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(100, 'delete discounts', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(101, 'view dishes', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(102, 'create dishes', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(103, 'update dishes', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(104, 'delete dishes', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(105, 'view dish_addons', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(106, 'create dish_addons', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(107, 'update dish_addons', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(108, 'delete dish_addons', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:16:12', 0),
(109, 'view dish_categories', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(110, 'create dish_categories', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(111, 'update dish_categories', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(112, 'delete dish_categories', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(113, 'view dish_details', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(114, 'create dish_details', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(115, 'update dish_details', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(116, 'delete dish_details', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(117, 'view dish_discount', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(118, 'create dish_discount', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(119, 'update dish_discount', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(120, 'delete dish_discount', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(125, 'view einvoices', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(126, 'create einvoices', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(127, 'update einvoices', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(128, 'delete einvoices', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(129, 'view einvoice_settings', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(130, 'create einvoice_settings', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(131, 'update einvoice_settings', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(132, 'delete einvoice_settings', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(133, 'view employees', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:17:26', 0),
(134, 'create employees', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:17:47', 0),
(135, 'update employees', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(136, 'delete employees', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(137, 'view employee_floor_partitions', 'admin', '2024-12-16 12:06:17', '2025-01-22 08:35:40', 0),
(138, 'create employee_floor_partitions', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(139, 'update employee_floor_partitions', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(140, 'delete employee_floor_partitions', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(141, 'view employee_opening_balances', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:18:25', 1),
(142, 'create employee_opening_balances', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:18:39', 1),
(143, 'update employee_opening_balances', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:18:54', 1),
(144, 'delete employee_opening_balances', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:19:08', 1),
(145, 'view employee_schedules', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:19:20', 1),
(146, 'create employee_schedules', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:19:34', 1),
(147, 'update employee_schedules', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:19:58', 1),
(148, 'delete employee_schedules', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:20:12', 1),
(149, 'view excuses', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:20:24', 1),
(150, 'create excuses', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:20:38', 1),
(151, 'update excuses', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:21:00', 1),
(152, 'delete excuses', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:21:13', 1),
(153, 'view excuse_requests', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(154, 'create excuse_requests', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:21:28', 1),
(155, 'update excuse_requests', 'admin', '2024-12-16 12:06:17', '2024-12-26 07:21:40', 1),
(156, 'delete excuse_requests', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(157, 'view excuse_settings', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(158, 'create excuse_settings', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(159, 'update excuse_settings', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(160, 'delete excuse_settings', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(161, 'view floors', 'admin', '2024-12-16 12:06:17', '2025-01-22 08:36:03', 0),
(162, 'create floors', 'admin', '2024-12-16 12:06:17', '2025-01-22 08:36:22', 0),
(163, 'update floors', 'admin', '2024-12-16 12:06:17', '2025-01-22 08:36:49', 0),
(164, 'delete floors', 'admin', '2024-12-16 12:06:17', '2025-01-22 08:36:58', 0),
(165, 'view floor_partitions', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(166, 'create floor_partitions', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(167, 'update floor_partitions', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(168, 'delete floor_partitions', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(169, 'view gifts', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(170, 'create gifts', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(171, 'update gifts', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(172, 'delete gifts', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(173, 'view ingredients', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(174, 'create ingredients', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(175, 'update ingredients', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(176, 'delete ingredients', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(177, 'view leave_nationals', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(178, 'create leave_nationals', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(179, 'update leave_nationals', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(180, 'delete leave_nationals', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(181, 'view leave_requests', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(182, 'create leave_requests', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(183, 'update leave_requests', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(184, 'delete leave_requests', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(185, 'view leave_settings', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(186, 'create leave_settings', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(187, 'update leave_settings', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(188, 'delete leave_settings', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(189, 'view leave_types', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(190, 'create leave_types', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(191, 'update leave_types', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(192, 'delete leave_types', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(193, 'view lines', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(194, 'create lines', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(195, 'update lines', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(196, 'delete lines', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(197, 'view menu', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(198, 'create menu', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(199, 'update menu', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(200, 'delete menu', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(201, 'view notifications', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(202, 'create notifications', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(203, 'update notifications', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(204, 'delete notifications', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 1),
(205, 'view offers', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(206, 'create offers', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(207, 'update offers', 'admin', '2024-12-16 12:06:17', '2024-12-16 12:06:17', 0),
(208, 'delete offers', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(213, 'view opening_balance', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(214, 'create opening_balance', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(215, 'update opening_balance', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(216, 'delete opening_balance', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(217, 'view orders', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(218, 'create orders', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(219, 'update orders', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(220, 'delete orders', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(221, 'view order_addons', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(222, 'create order_addons', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(223, 'update order_addons', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(224, 'delete order_addons', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(225, 'view order_details', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(226, 'create order_details', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(227, 'update order_details', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(228, 'delete order_details', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(229, 'view order_products', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(230, 'create order_products', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(231, 'update order_products', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(232, 'delete order_products', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(233, 'view order_refunds', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(234, 'create order_refunds', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(235, 'update order_refunds', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(236, 'delete order_refunds', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(237, 'view order_trackings', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(238, 'create order_trackings', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(239, 'update order_trackings', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(240, 'delete order_trackings', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(241, 'view order_transactions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(242, 'create order_transactions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(243, 'update order_transactions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(244, 'delete order_transactions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(245, 'view overtime_settings', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(246, 'create overtime_settings', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(247, 'update overtime_settings', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(248, 'delete overtime_settings', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(249, 'view overtime_types', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(250, 'create overtime_types', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(251, 'update overtime_types', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(252, 'delete overtime_types', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(253, 'view payrolls', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(254, 'create payrolls', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(255, 'update payrolls', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(256, 'delete payrolls', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(257, 'view penalties', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(258, 'create penalties', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(259, 'update penalties', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(260, 'delete penalties', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(261, 'view penalty_deductions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(262, 'create penalty_deductions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(263, 'update penalty_deductions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(264, 'delete penalty_deductions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(265, 'view penalty_reasons', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(266, 'create penalty_reasons', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(267, 'update penalty_reasons', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(268, 'delete penalty_reasons', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(269, 'view permissions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(270, 'create permissions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(271, 'update permissions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(272, 'delete permissions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(273, 'view point_products', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(274, 'create point_products', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(275, 'update point_products', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(276, 'delete point_products', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(277, 'view point_systems', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(278, 'create point_systems', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(279, 'update point_systems', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(280, 'delete point_systems', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(281, 'view point_transactions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(282, 'create point_transactions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(283, 'update point_transactions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(284, 'delete point_transactions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 1),
(285, 'view positions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(286, 'create positions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(287, 'update positions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(288, 'delete positions', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(289, 'view products', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(290, 'create products', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(291, 'update products', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(292, 'delete products', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(293, 'view product_colors', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(294, 'create product_colors', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(295, 'update product_colors', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(296, 'delete product_colors', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(297, 'view product_images', 'admin', '2024-12-16 12:06:18', '2024-12-16 12:06:18', 0),
(298, 'create product_images', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(299, 'update product_images', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(300, 'delete product_images', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(301, 'view product_limit', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(302, 'create product_limit', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(303, 'update product_limit', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(304, 'delete product_limit', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(305, 'view product_sizes', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(306, 'create product_sizes', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(307, 'update product_sizes', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(308, 'delete product_sizes', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(309, 'view product_transactions', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(310, 'create product_transactions', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(311, 'update product_transactions', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(312, 'delete product_transactions', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(313, 'view product_transaction_logs', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(314, 'create product_transaction_logs', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(315, 'update product_transaction_logs', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(316, 'delete product_transaction_logs', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(317, 'view product_units', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(318, 'create product_units', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(319, 'update product_units', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(320, 'delete product_units', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(321, 'view purchase_invoices', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(322, 'create purchase_invoices', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(323, 'update purchase_invoices', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(324, 'delete purchase_invoices', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(325, 'view purchase_invoices_details', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(326, 'create purchase_invoices_details', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(327, 'update purchase_invoices_details', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(328, 'delete purchase_invoices_details', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(329, 'view recipes', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(330, 'create recipes', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(331, 'update recipes', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(332, 'delete recipes', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(333, 'view recipe_images', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(334, 'create recipe_images', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(335, 'update recipe_images', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(336, 'delete recipe_images', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(337, 'view roles', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(338, 'create roles', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(339, 'update roles', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(340, 'delete roles', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(341, 'view settings', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(342, 'create settings', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(343, 'update settings', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(344, 'delete settings', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(345, 'view shelves', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(346, 'create shelves', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(347, 'update shelves', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(348, 'delete shelves', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(349, 'view shifts', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(350, 'create shifts', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(351, 'update shifts', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(352, 'delete shifts', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(353, 'view shift_details', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(354, 'create shift_details', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(355, 'update shift_details', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(356, 'delete shift_details', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(357, 'view sizes', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(358, 'create sizes', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(359, 'update sizes', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(360, 'delete sizes', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 0),
(361, 'view stores', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(362, 'create stores', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(363, 'update stores', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(364, 'delete stores', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(365, 'view store_categories', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(366, 'create store_categories', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(367, 'update store_categories', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(368, 'delete store_categories', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(369, 'view store_transactions', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(370, 'create store_transactions', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(371, 'update store_transactions', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(372, 'delete store_transactions', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(373, 'view store_transaction_details', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(374, 'create store_transaction_details', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(375, 'update store_transaction_details', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(376, 'delete store_transaction_details', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(377, 'view tables', 'admin', '2024-12-16 12:06:19', '2025-01-22 08:37:22', 0),
(378, 'create tables', 'admin', '2024-12-16 12:06:19', '2025-01-22 08:37:31', 0),
(379, 'update tables', 'admin', '2024-12-16 12:06:19', '2025-01-22 08:37:40', 0),
(380, 'delete tables', 'admin', '2024-12-16 12:06:19', '2025-01-22 08:37:52', 0),
(381, 'view table_reservations', 'admin', '2024-12-16 12:06:19', '2025-01-22 08:38:02', 0),
(382, 'create table_reservations', 'admin', '2024-12-16 12:06:19', '2025-01-22 08:38:14', 0),
(383, 'update table_reservations', 'admin', '2024-12-16 12:06:19', '2025-01-22 08:38:23', 0),
(384, 'delete table_reservations', 'admin', '2024-12-16 12:06:19', '2025-01-22 08:38:32', 0),
(385, 'view timetables', 'admin', '2024-12-16 12:06:19', '2024-12-16 12:06:19', 1),
(386, 'create timetables', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 1),
(387, 'update timetables', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 1),
(388, 'delete timetables', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 1),
(389, 'view units', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(390, 'create units', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(391, 'update units', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(392, 'delete units', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(393, 'view users', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(394, 'create users', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(395, 'update users', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(396, 'delete users', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(397, 'view user_gifts', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(398, 'create user_gifts', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(399, 'update user_gifts', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(400, 'delete user_gifts', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(401, 'view vendors', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 1),
(402, 'create vendors', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 1),
(403, 'update vendors', 'admin', '2024-12-16 12:06:20', '2024-12-26 10:46:26', 1),
(404, 'delete vendors', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 1),
(405, 'view logos', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(406, 'create logos', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(407, 'update logos', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(408, 'delete logos', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(409, 'view sliders', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(410, 'create sliders', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(411, 'update sliders', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(412, 'delete sliders', 'admin', '2024-12-16 12:06:20', '2024-12-16 12:06:20', 0),
(413, 'view terms', 'admin', '2024-12-18 09:33:38', '2024-12-18 09:35:32', 0),
(414, 'create terms', 'admin', '2024-12-18 09:36:09', '2024-12-18 09:36:09', 0),
(415, 'update terms', 'admin', '2024-12-18 09:36:34', '2024-12-18 09:36:34', 0),
(416, 'delete terms', 'admin', '2024-12-18 09:37:12', '2024-12-18 09:37:12', 0),
(417, 'view privacies', 'admin', '2024-12-18 09:37:52', '2024-12-18 09:37:52', 0),
(418, 'create privacies', 'admin', '2024-12-18 09:38:09', '2024-12-18 09:38:09', 0),
(419, 'update privacies', 'admin', '2024-12-18 09:38:26', '2024-12-18 09:38:26', 0),
(420, 'delete privacies', 'admin', '2024-12-18 09:38:43', '2024-12-18 09:38:43', 0),
(421, 'view returns', 'admin', '2024-12-18 10:21:32', '2024-12-18 10:21:32', 0),
(422, 'create returns', 'admin', '2024-12-18 10:21:51', '2024-12-18 10:21:51', 0),
(423, 'update returns', 'admin', '2024-12-18 10:22:23', '2024-12-18 10:22:23', 0),
(424, 'delete returns', 'admin', '2024-12-18 10:22:46', '2024-12-18 10:22:46', 0),
(425, 'view discount_dishes', 'admin', '2024-12-23 05:58:29', '2024-12-23 05:58:29', 0),
(426, 'view offerDetails', 'admin', '2024-12-23 10:43:24', '2024-12-23 10:43:24', 0),
(427, 'create offerDetails', 'admin', '2024-12-23 10:44:08', '2024-12-23 10:44:08', 0),
(428, 'update offerDetails', 'admin', '2024-12-23 10:44:25', '2024-12-23 10:44:25', 0),
(429, 'delete offerDetails', 'admin', '2024-12-23 10:44:40', '2024-12-23 10:44:40', 0),
(430, 'create faqs', 'admin', '2024-12-25 09:52:08', '2024-12-25 09:52:08', 0),
(431, 'view faqs', 'admin', '2024-12-25 09:52:32', '2024-12-25 09:52:32', 0),
(432, 'update faqs', 'admin', '2024-12-25 09:52:57', '2024-12-25 09:52:57', 0),
(433, 'delete faqs', 'admin', '2024-12-25 09:54:04', '2024-12-25 09:54:04', 0),
(434, 'view rates', 'admin', '2024-12-25 09:54:38', '2024-12-25 09:54:38', 0),
(435, 'create rates', 'admin', '2024-12-25 09:55:08', '2024-12-25 09:55:08', 0),
(436, 'update rates', 'admin', '2024-12-25 09:55:37', '2024-12-25 09:55:37', 0),
(437, 'delete rates', 'admin', '2024-12-25 09:56:06', '2024-12-25 09:56:06', 0),
(440, 'view branch_menu_categories', 'admin', '2025-01-20 09:57:06', '2025-01-20 09:57:06', 0),
(441, 'view branch_menus', 'admin', '2025-01-20 10:00:25', '2025-01-20 10:00:25', 0),
(442, 'create branch_menus', 'admin', '2025-01-20 10:01:01', '2025-01-20 10:01:01', 0),
(443, 'update branch_menus', 'admin', '2025-01-20 10:01:33', '2025-01-20 10:01:33', 0),
(444, 'delete branch_menus', 'admin', '2025-01-20 10:02:00', '2025-01-20 10:02:00', 0),
(445, 'create branch_menu_categories', 'admin', '2025-01-20 10:02:22', '2025-01-20 10:02:22', 0),
(446, 'update branch_menu_categories', 'admin', '2025-01-20 10:02:43', '2025-01-20 10:02:43', 0),
(447, 'delete branch_menu_categories', 'admin', '2025-01-20 10:03:10', '2025-01-20 10:03:10', 0),
(448, 'view report_best_seller_dishes', 'admin', '2025-01-20 10:08:16', '2025-01-20 10:08:16', 0),
(449, 'view addon_categories', 'admin', '2025-01-20 10:40:32', '2025-01-20 10:40:32', 0),
(450, 'print purchase_invoice', 'admin', '2025-01-20 10:40:55', '2025-01-20 10:40:55', 0),
(451, 'edit addon_categorie', 'admin', '2025-01-20 10:41:44', '2025-01-20 10:41:44', 0),
(452, 'view units_products', 'admin', '2025-01-20 10:43:36', '2025-01-20 10:43:36', 0),
(453, 'delete addon_categories', 'admin', '2025-01-20 10:44:34', '2025-01-20 10:44:34', 0),
(454, 'print advances', 'admin', '2025-01-20 10:45:02', '2025-01-20 10:45:02', 0),
(455, 'print invoice', 'admin', '2025-01-20 10:45:52', '2025-01-20 10:45:52', 0),
(456, 'view addons', 'admin', '2025-01-20 10:47:10', '2025-01-20 10:47:10', 0),
(457, 'view branch_menu_addons', 'admin', '2025-01-20 11:05:45', '2025-01-20 11:05:45', 0),
(458, 'create branch_menu_addons', 'admin', '2025-01-20 11:06:08', '2025-01-20 11:06:08', 0),
(459, 'update branch_menu_addons', 'admin', '2025-01-20 11:06:41', '2025-01-20 11:06:41', 0),
(460, 'delete branch_menu_addons', 'admin', '2025-01-20 11:07:08', '2025-01-20 11:07:08', 0),
(461, 'view branch_menu_sizes', 'admin', '2025-01-20 11:07:30', '2025-01-20 11:07:30', 0),
(462, 'create branch_menu_sizes\'', 'admin', '2025-01-20 11:07:55', '2025-01-20 11:07:55', 0),
(463, 'update branch_menu_sizes', 'admin', '2025-01-20 11:08:14', '2025-01-20 11:08:14', 0),
(464, 'delete branch_menu_sizes', 'admin', '2025-01-20 11:08:34', '2025-01-20 11:08:34', 0),
(465, 'view clients_reports', 'admin', '2025-01-20 11:09:21', '2025-01-20 11:09:21', 0),
(466, 'view purchasing_reports', 'admin', '2025-01-20 11:09:42', '2025-01-20 11:09:42', 0),
(467, 'view report_most_customer_place_order', 'admin', '2025-01-20 11:10:14', '2025-01-20 11:10:14', 0),
(468, 'create order_settings', 'admin', '2025-01-20 11:11:07', '2025-01-20 11:11:07', 0),
(469, 'update order_settings', 'admin', '2025-01-20 11:11:26', '2025-01-20 11:11:26', 0),
(470, 'view order_settings', 'admin', '2025-01-20 11:11:50', '2025-01-20 11:11:50', 0),
(471, 'active branch_menu_categories', 'admin', '2025-01-20 14:57:35', '2025-01-22 10:52:46', 0),
(472, 'active branch_menus', 'admin', '2025-01-20 14:58:45', '2025-01-22 10:52:20', 0),
(473, 'active branch_menu_addons', 'admin', '2025-01-20 14:59:08', '2025-01-22 10:51:09', 0),
(474, 'active branch_menu_sizes', 'admin', '2025-01-20 14:59:37', '2025-01-22 10:51:35', 0),
(475, 'restore addon_categories', 'admin', '2025-01-21 08:28:32', '2025-01-21 08:28:32', 0),
(476, 'print orders', 'admin', '2025-01-21 09:03:23', '2025-01-21 09:03:23', 0),
(477, 'download orders', 'admin', '2025-01-21 09:04:42', '2025-01-21 09:04:42', 0),
(478, 'update addons', 'admin', '2025-01-22 07:41:44', '2025-01-22 07:41:44', 0),
(479, 'delete addons', 'admin', '2025-01-22 07:42:03', '2025-01-22 07:42:03', 0),
(480, 'restore addons', 'admin', '2025-01-22 07:42:24', '2025-01-22 07:42:24', 0),
(481, 'create addons', 'admin', '2025-01-22 07:42:44', '2025-01-22 07:42:44', 0),
(482, 'restore cuisines', 'admin', '2025-01-22 08:21:04', '2025-01-22 08:21:04', 0),
(483, 'print purchase_invoices', 'admin', '2025-01-22 08:35:00', '2025-01-22 08:35:00', 0),
(484, 'import employess', 'admin', '2025-01-22 10:03:36', '2025-01-22 10:03:36', 0),
(485, 'save report_pdf', 'admin', '2025-01-22 10:32:51', '2025-01-22 10:32:51', 0),
(486, 'detail report_orders', 'admin', '2025-01-22 10:36:42', '2025-01-28 11:47:26', 0),
(487, 'view branch_menu_category_addons', 'admin', '2025-01-26 09:11:36', '2025-01-26 09:11:36', 0),
(488, 'create branch_menu_category_addons', 'admin', '2025-01-26 09:12:26', '2025-01-26 09:12:26', 0),
(489, 'update branch_menu_category_addons', 'admin', '2025-01-26 09:12:53', '2025-01-26 09:12:53', 0),
(490, 'changestatus branch_menu_category_addons', 'admin', '2025-01-26 09:13:42', '2025-01-26 09:13:42', 0),
(491, 'detail report_best_seller_dishes', 'admin', '2025-01-27 14:15:04', '2025-01-27 14:15:04', 0),
(492, 'view report_branches', 'admin', '2025-01-27 14:21:16', '2025-01-27 14:21:16', 0),
(493, 'detail report_branches', 'admin', '2025-01-27 14:22:42', '2025-01-27 14:22:42', 0),
(494, 'view report_orders', 'admin', '2025-01-28 11:53:06', '2025-01-28 11:53:06', 0);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `point_products`
--

CREATE TABLE `point_products` (
  `id` bigint UNSIGNED NOT NULL,
  `dish_id` bigint UNSIGNED NOT NULL,
  `point_num` int NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `expire` date NOT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `point_systems`
--

CREATE TABLE `point_systems` (
  `id` bigint UNSIGNED NOT NULL,
  `type_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value_redeem` decimal(10,2) DEFAULT NULL,
  `active` int NOT NULL DEFAULT '1' COMMENT '1 = active, 0 = inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `value_earn` decimal(10,2) DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `point_redeem` decimal(10,2) DEFAULT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `point_systems`
--

INSERT INTO `point_systems` (`id`, `type_en`, `type_ar`, `value_redeem`, `active`, `created_at`, `updated_at`, `value_earn`, `created_by`, `modified_by`, `deleted_by`, `deleted_at`, `point_redeem`, `branch_id`) VALUES
(2, 'total of order value', 'بأجمالى الفاتوره', NULL, 1, '2025-01-08 06:18:54', '2025-01-08 06:18:54', 1.00, NULL, NULL, NULL, NULL, 1.00, NULL),
(3, 'total of order value', 'بأجمالى الفاتوره', NULL, 1, '2025-01-08 06:19:09', '2025-01-08 06:19:09', 1.00, NULL, NULL, NULL, NULL, 1.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `point_transactions`
--

CREATE TABLE `point_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `type` enum('earn','redeem') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` bigint UNSIGNED NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `privacy_policies`
--

CREATE TABLE `privacy_policies` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `privacy_policies`
--

INSERT INTO `privacy_policies` (`id`, `name_ar`, `name_en`, `description_ar`, `description_en`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `active`) VALUES
('11cf3bde-805d-41b9-9619-b73f582c74b2', 'المعلومات التي نجمعها', 'Information We Collect', '<p>معلومات الاتصال: على سبيل المثال، قد نجمع اسمك وعنوان شارعك. قد نجمع أيضًا رقم هاتفك أو عنوان بريدك الإلكتروني. معلومات الدفع: على سبيل المثال، قد نجمع معلومات بطاقتك الائتمانية عند إجراء عملية شراء</p>', '<p>Contact information: For example, we may collect your name and street address. We may also collect your phone number or email address. Payment information: For example, we may collect your credit card information when you make a purchase</p>', 1, 9, NULL, '2024-12-18 09:40:40', '2025-01-21 12:04:41', NULL, 1),
('597316c1-0bea-4c3f-b0af-d76b15941b06', 'سياسة الخصوصية 2', 'Privacy Policy 2', '<p>وصف سياسة الخصوصية 2</p>', '<p>Privacy Policy Description 2</p>', 1, 9, NULL, '2024-12-22 11:26:51', '2025-01-15 09:16:55', NULL, 0),
('a12c5e30-ff66-4d9c-939e-ce793fa14e72', 'j', 'Term 3', '<p>&nbsp;j</p>', '<p>h</p>', 1, 1, NULL, '2024-12-24 08:31:57', '2024-12-24 08:32:24', '2024-12-24 08:32:24', 0),
('ca021e2d-401c-43c2-8499-bd9648d2ad72', 'سياسة الخصوصية 2', 'Privacy policy 2', '<p>وصف</p>', '<p>description</p>', 1, 1, NULL, '2024-12-22 11:12:08', '2024-12-22 11:25:40', '2024-12-22 11:25:40', 0);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `brand_id` bigint UNSIGNED DEFAULT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `main_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('complete','raw') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `main_unit_id` bigint UNSIGNED NOT NULL,
  `currency_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `is_valid` tinyint(1) NOT NULL DEFAULT '1',
  `sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_remind` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED NOT NULL,
  `modify_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_have_expired` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `brand_id`, `name_ar`, `name_en`, `description_ar`, `description_en`, `main_image`, `type`, `main_unit_id`, `currency_code`, `category_id`, `is_valid`, `sku`, `barcode`, `code`, `is_remind`, `created_by`, `modify_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `is_have_expired`) VALUES
(19, 7, 'منتج 1', 'product1', 'منتج 1', 'product 1', 'https://erpsystem.testdomain100.online/images/brands/81736413641.png', 'raw', 16, 'EGP', 4, 1, '123', '123', '0000', 1, 1, NULL, NULL, '2025-01-08 10:01:43', '2025-01-09 07:57:35', '2025-01-09 07:57:35', 1),
(20, 7, 'مياه', 'water', NULL, NULL, 'https://erpsystem.testdomain100.online/images/products/211736413248.png', 'complete', 16, 'EGP', 5, 1, '141', '141', '0001', 1, 1, NULL, NULL, '2025-01-08 12:11:55', '2025-01-13 10:52:19', '2025-01-13 10:52:19', 1),
(21, 7, 'منتج 1', 'Product 1', 'وصف منتج 1', 'description of product 1', 'https://erpsystem.testdomain100.online/images/products/211736413248.png', 'raw', 16, 'EUR', 1, 1, '123', '123', '0002', 1, 9, NULL, NULL, '2025-01-09 08:00:48', '2025-01-14 10:46:36', NULL, 1),
(22, 7, 'منتج 2', 'Product 2', 'وصف منتج 2', 'description of product 2', 'https://erpsystem.testdomain100.online/images/products/221736750559.png', 'complete', 16, 'USD', 3, 1, '2', '2', '0003', 1, 9, NULL, NULL, '2025-01-13 05:41:45', '2025-01-13 10:56:35', '2025-01-13 10:56:35', 1),
(23, 7, 'new', 'new', 'Part-Time', 'Part-Time', 'https://erpsystem.testdomain100.online/images/products/231736769464.png', 'raw', 16, 'MXN', 1, 1, 'aa', 'Part-Time', '0004', 1, 9, NULL, NULL, '2025-01-13 10:57:44', '2025-01-13 11:02:28', '2025-01-13 11:02:28', 1),
(24, 7, 'new', 'new', NULL, NULL, 'https://erpsystem.testdomain100.online/images/products/241736770012.png', 'complete', 16, 'MXN', 2, 1, 'cc', 'mm', '0005', 1, 9, NULL, NULL, '2025-01-13 11:06:51', '2025-01-13 11:07:28', '2025-01-13 13:22:29', 1),
(25, 7, 'منتج 2', 'product 2', 'وصف', 'description', 'https://erpsystem.testdomain100.online/images/products/251736855086.png', 'complete', 16, 'USD', 3, 1, '1234', '1234', '0006', 1, 9, NULL, NULL, '2025-01-14 10:44:46', '2025-01-14 10:44:46', NULL, 1),
(26, 7, 'new', 'new', '21', '21', 'https://erpsystem.testdomain100.online/images/products/261736939290.png', 'raw', 16, 'KWD', 1, 1, '21', '21', '0007', 1, 9, NULL, NULL, '2025-01-15 12:08:10', '2025-01-15 12:08:10', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_colors`
--

CREATE TABLE `product_colors` (
  `id` bigint UNSIGNED NOT NULL,
  `color_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modify_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_colors`
--

INSERT INTO `product_colors` (`id`, `color_id`, `product_id`, `deleted_by`, `created_by`, `modify_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5, 2, 19, NULL, 9, NULL, '2025-01-09 07:41:31', '2025-01-09 07:41:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `modify_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_limit`
--

CREATE TABLE `product_limit` (
  `id` bigint UNSIGNED NOT NULL,
  `min_limit` decimal(8,2) NOT NULL,
  `max_limit` decimal(8,2) NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `store_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_limit`
--

INSERT INTO `product_limit` (`id`, `min_limit`, `max_limit`, `product_id`, `created_at`, `updated_at`, `store_id`) VALUES
(16, 1.00, 2.00, 19, '2025-01-08 10:01:43', '2025-01-08 10:01:43', NULL),
(17, 1.00, 3.00, 20, '2025-01-08 12:11:55', '2025-01-08 12:11:55', NULL),
(18, 1.00, 2.00, 21, '2025-01-09 08:00:48', '2025-01-09 08:00:48', NULL),
(19, 1.00, 5.00, 22, '2025-01-13 05:41:45', '2025-01-13 05:41:45', NULL),
(20, 10.00, 20.00, 23, '2025-01-13 10:57:44', '2025-01-13 10:57:44', NULL),
(21, 10.00, 20.00, 24, '2025-01-13 11:06:52', '2025-01-13 11:06:52', NULL),
(22, 1.00, 3.00, 25, '2025-01-14 10:44:46', '2025-01-14 10:44:46', NULL),
(23, 1.00, 21.00, 26, '2025-01-15 12:08:10', '2025-01-15 12:08:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_sizes`
--

CREATE TABLE `product_sizes` (
  `id` bigint UNSIGNED NOT NULL,
  `size_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `code_size` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `modify_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_sizes`
--

INSERT INTO `product_sizes` (`id`, `size_id`, `product_id`, `code_size`, `created_by`, `deleted_by`, `modify_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 7, 19, '1', 9, NULL, NULL, '2025-01-09 07:41:22', '2025-01-09 07:41:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_transactions`
--

CREATE TABLE `product_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `store_id` bigint UNSIGNED NOT NULL,
  `product_size_id` bigint UNSIGNED DEFAULT NULL,
  `product_color_id` bigint UNSIGNED DEFAULT NULL,
  `count` int NOT NULL,
  `expired_date` date DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_transaction_logs`
--

CREATE TABLE `product_transaction_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `store_id` bigint UNSIGNED NOT NULL,
  `product_size_id` bigint UNSIGNED DEFAULT NULL,
  `product_color_id` bigint UNSIGNED DEFAULT NULL,
  `count` double(8,2) NOT NULL,
  `expired_date` date DEFAULT NULL,
  `model_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1 for outgoing, 2 for incoming',
  `order_id` int DEFAULT NULL,
  `transaction_type` int DEFAULT '1' COMMENT '1 order, 2 refund order, 3 purchase, 4 refund purchase',
  `order_details_id` int DEFAULT NULL,
  `order_type` int DEFAULT '1' COMMENT '1 product, 2 dish, 3 addon',
  `created_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_units`
--

CREATE TABLE `product_units` (
  `id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `factor` double(8,2) NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modify_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_units`
--

INSERT INTO `product_units` (`id`, `unit_id`, `product_id`, `factor`, `created_by`, `modify_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 16, 19, 4.00, 1, NULL, NULL, '2025-01-08 10:02:00', '2025-01-08 10:02:00', NULL),
(5, 16, 21, 2.00, 1, NULL, NULL, '2025-01-13 09:54:30', '2025-01-13 09:54:30', NULL),
(6, 16, 22, 1.00, 9, NULL, NULL, '2025-01-13 10:51:43', '2025-01-13 10:51:43', NULL),
(7, 16, 23, 1.00, 9, NULL, NULL, '2025-01-13 11:01:36', '2025-01-13 11:01:36', NULL),
(8, 16, 24, 1.00, 9, NULL, NULL, '2025-01-13 11:07:09', '2025-01-13 11:07:09', NULL),
(9, 16, 25, 1.00, 9, NULL, NULL, '2025-01-14 10:45:03', '2025-01-14 10:45:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_invoices`
--

CREATE TABLE `purchase_invoices` (
  `id` bigint UNSIGNED NOT NULL,
  `Date` date NOT NULL,
  `invoice_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendor_id` bigint UNSIGNED NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:Purchase, 1:Refund',
  `store_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_invoices_details`
--

CREATE TABLE `purchase_invoices_details` (
  `id` bigint UNSIGNED NOT NULL,
  `purchase_invoices_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `total_price` decimal(15,2) GENERATED ALWAYS AS ((`price` * `quantity`)) VIRTUAL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rates`
--

CREATE TABLE `rates` (
  `id` bigint UNSIGNED NOT NULL,
  `value` int NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rates`
--

INSERT INTO `rates` (`id`, `value`, `note`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `active`) VALUES
(10, 5, NULL, 1, NULL, NULL, '2025-01-08 12:18:45', '2025-01-08 12:18:45', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` bigint UNSIGNED NOT NULL,
  `meal_type` enum('breakfast','lunch','dinner','snack','dessert') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lunch',
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '1: Recipe, 2: Addon',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `meal_type`, `name_en`, `name_ar`, `description_en`, `description_ar`, `type`, `is_active`, `created_by`, `modified_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`, `code`) VALUES
(15, 'lunch', 'extra cheese', 'اضافه جبنه', NULL, NULL, 2, 1, 1, 67, NULL, NULL, '2025-01-08 11:50:21', '2025-01-21 07:50:01', '0001'),
(16, 'lunch', 'sauce', 'اضافه صوص', NULL, NULL, 2, 1, 1, 9, NULL, NULL, '2025-01-08 11:51:38', '2025-01-13 10:52:52', '0002'),
(17, 'lunch', 'pizza', 'بيتزا', NULL, NULL, 1, 1, 1, 67, NULL, NULL, '2025-01-08 11:52:17', '2025-01-21 07:49:48', '0003'),
(18, 'lunch', 'onion rings', 'اضافة حلقات بصل', 'description', 'وصف', 2, 1, 9, 9, NULL, NULL, '2025-01-09 08:03:53', '2025-01-21 11:04:18', '0004'),
(19, 'lunch', 'add on 3', 'اضافة حلقات بصل', NULL, NULL, 2, 1, 9, NULL, 9, '2025-01-09 08:37:58', '2025-01-09 08:04:04', '2025-01-09 08:37:58', '0005'),
(20, 'lunch', 'add on 3', 'اضافة حلقات بصل', NULL, NULL, 2, 1, 9, NULL, 9, '2025-01-09 08:38:04', '2025-01-09 08:04:15', '2025-01-09 08:38:04', '0006'),
(21, 'lunch', 'add on 3', 'اضافة حلقات بصل', NULL, NULL, 2, 1, 9, NULL, 9, '2025-01-09 08:38:11', '2025-01-09 08:04:59', '2025-01-09 08:38:11', '0007'),
(22, 'lunch', 'new', 'new', 'new', 'new', 2, 1, 9, 9, 9, '2025-01-13 10:56:27', '2025-01-13 05:43:34', '2025-01-13 10:56:27', '0008'),
(29, 'lunch', 'yy', 'ii', NULL, NULL, 1, 1, 1, 1, NULL, NULL, '2025-01-13 09:55:08', '2025-01-22 13:10:15', '0009'),
(30, 'lunch', 'new', 'new', NULL, NULL, 1, 1, 9, 9, 9, '2025-01-13 10:57:05', '2025-01-13 10:52:04', '2025-01-13 10:57:05', '0010'),
(32, 'lunch', 'new', 'new', NULL, NULL, 1, 1, 9, NULL, 9, '2025-01-13 11:02:15', '2025-01-13 11:01:48', '2025-01-13 11:02:15', '0011'),
(33, 'lunch', 'new', 'new', NULL, NULL, 1, 1, 9, 9, NULL, NULL, '2025-01-13 11:07:39', '2025-01-14 11:01:50', '0012'),
(34, 'lunch', 'spicy', 'حار', 'desc', 'وصف', 2, 1, 9, 1, NULL, NULL, '2025-01-14 10:59:26', '2025-01-22 11:30:43', '0013'),
(35, 'lunch', 'new', 'new', NULL, NULL, 2, 1, 9, 9, 9, '2025-01-19 10:38:34', '2025-01-15 12:09:07', '2025-01-19 10:38:34', '0014'),
(36, 'lunch', 'water', 'ماء', NULL, NULL, 1, 1, 1, NULL, NULL, NULL, '2025-01-15 15:18:17', '2025-01-15 15:18:17', '0015'),
(37, 'lunch', 'water', 'مياة', NULL, NULL, 1, 1, 1, 1, NULL, NULL, '2025-01-16 12:11:10', '2025-01-22 13:10:46', '0016'),
(38, 'lunch', 'a', 'a', NULL, NULL, 1, 1, 9, NULL, 9, '2025-01-20 13:54:01', '2025-01-19 10:39:21', '2025-01-20 13:54:01', '0017'),
(39, 'lunch', 'Stacy Kent', 'Liberty Lynn', NULL, NULL, 1, 0, 1, NULL, 1, '2025-01-19 14:57:47', '2025-01-19 14:41:51', '2025-01-19 14:57:47', '0018'),
(40, 'lunch', 'aaa', 'a', 'a', NULL, 2, 1, 9, 9, 9, '2025-01-20 13:44:06', '2025-01-20 13:43:37', '2025-01-20 13:44:06', '0019'),
(41, 'lunch', 'a', 'a', NULL, NULL, 1, 1, 9, NULL, 9, '2025-01-20 13:54:23', '2025-01-20 13:53:56', '2025-01-20 13:54:23', '0020'),
(42, 'lunch', 'a', 'a', NULL, NULL, 1, 1, 9, 9, 9, '2025-01-21 12:09:09', '2025-01-21 12:08:37', '2025-01-21 12:09:09', '0021'),
(43, 'lunch', 'Stella Cash', 'Xantha Warren', 'Velit eos do offic', 'Rerum veritatis expe', 2, 1, 1, NULL, 1, '2025-01-22 11:29:49', '2025-01-22 11:29:42', '2025-01-22 11:29:49', '0022'),
(44, 'lunch', 'Fredericka Smith', 'Salvador Guthrie', 'yy', 'uu', 2, 0, 1, 1, 1, '2025-01-22 12:13:46', '2025-01-22 11:32:56', '2025-01-22 12:13:46', '0023'),
(45, 'lunch', 'Anne Church', 'Iona Patrick', NULL, NULL, 2, 0, 1, NULL, 1, '2025-01-22 11:37:42', '2025-01-22 11:33:02', '2025-01-22 11:37:42', '0024'),
(46, 'lunch', 'Anne Church', 'Iona Patrick', NULL, NULL, 2, 0, 1, NULL, 1, '2025-01-22 11:37:39', '2025-01-22 11:33:05', '2025-01-22 11:37:39', '0025'),
(47, 'lunch', 'Anne Church', 'Iona Patrick', 'tt', 'jj', 2, 0, 1, NULL, 1, '2025-01-22 11:37:35', '2025-01-22 11:33:14', '2025-01-22 11:37:35', '0026'),
(48, 'lunch', 'Natalie Sharpe', 'Philip Suarez', 'Repudiandae consequa', 'Unde cupiditate numq', 2, 1, 1, 1, 1, '2025-01-22 12:13:42', '2025-01-22 12:12:25', '2025-01-22 12:13:42', '0027'),
(49, 'lunch', 'Jermaine Bright', 'Paul Rivas', 'Praesentium quas fug', 'Autem in duis deseru', 2, 1, 1, NULL, 1, '2025-01-22 12:13:59', '2025-01-22 12:13:54', '2025-01-22 12:13:59', '0028'),
(50, 'lunch', 'Yael Santana', 'Vivien Greer', 'bbbbbbbbbb', 'aaaaaaaaa', 2, 1, 1, 9, 9, '2025-01-26 14:17:41', '2025-01-22 13:09:18', '2025-01-26 14:17:41', '0029'),
(51, 'lunch', 'Quinlan Ayala', 'Brielle Holt', 'rr', 'yy', 1, 1, 1, NULL, 1, '2025-01-22 13:38:29', '2025-01-22 13:25:57', '2025-01-22 13:38:29', '0030'),
(52, 'lunch', 'new', 'new', 'aa', 'aa', 1, 1, 9, NULL, 9, '2025-01-27 14:34:51', '2025-01-26 11:24:09', '2025-01-27 14:34:51', '0031'),
(54, 'lunch', 'test', 'test', 'aa', 'aa', 1, 1, 9, NULL, 9, '2025-01-27 14:34:46', '2025-01-26 11:27:52', '2025-01-27 14:34:46', '0032'),
(55, 'lunch', 'aa', 'aa', 'aa', 'aa', 1, 1, 9, NULL, 9, '2025-01-29 08:50:51', '2025-01-29 08:49:42', '2025-01-29 08:50:51', '0033'),
(56, 'lunch', 'Kasper Carter', 'Yolanda Holcomb', 'Dolor ex quo lorem q', 'Nisi dolorem nulla s', 1, 1, 1, NULL, NULL, NULL, '2025-01-29 13:01:07', '2025-01-29 13:01:07', NULL),
(57, 'lunch', 'Hunter Carson', 'Ruby Cline', 'Sed eum consequatur', 'Nostrum quidem volup', 1, 1, 1, NULL, NULL, NULL, '2025-01-29 14:09:39', '2025-01-29 14:09:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `recipe_images`
--

CREATE TABLE `recipe_images` (
  `id` bigint UNSIGNED NOT NULL,
  `recipe_id` bigint UNSIGNED NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recipe_images`
--

INSERT INTO `recipe_images` (`id`, `recipe_id`, `image_path`, `created_at`, `updated_at`) VALUES
(5, 15, 'images/addons/dish.jpg', '2025-01-08 11:50:21', '2025-01-08 11:50:21'),
(6, 17, 'images/recipes/677e83117a983_dish.jpg', '2025-01-08 11:52:17', '2025-01-08 11:52:17'),
(8, 33, 'images/recipes/logo-with-white-bg.png', '2025-01-15 13:00:11', '2025-01-15 13:00:11'),
(9, 36, 'images/addons/1.PNG', '2025-01-15 15:18:17', '2025-01-15 15:18:17'),
(10, 37, 'images/addons/images.jpg', '2025-01-16 12:11:10', '2025-01-16 12:11:10');

-- --------------------------------------------------------

--
-- Table structure for table `return_policies`
--

CREATE TABLE `return_policies` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `return_policies`
--

INSERT INTO `return_policies` (`id`, `name_ar`, `name_en`, `description_ar`, `description_en`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `active`) VALUES
('8c50e350-f541-4a98-bbbd-7c797b43d02e', 'سياسة الاسترجاع 2', 'Return Policy 2', '<p>وصف سياسة الاسترجاع 2</p>', '<p>Return Policy Description 2</p>', 1, 9, NULL, '2024-12-22 11:28:15', '2024-12-31 12:47:00', NULL, 1),
('ae1aafd9-d966-4658-ba6b-aac5161622f3', 'سياسة الاسترجاع 1', 'Return Policy 1', '<p>وصف سياسة الاسترجاع 1</p>', '<p>Return Policy Description 1</p>', 1, 9, NULL, '2024-12-18 10:24:16', '2024-12-31 12:47:08', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'superAdmin', 'admin', '2025-01-08 06:16:27', '2025-01-08 06:16:27'),
(2, 'LocalWork Admin', 'admin', '2025-01-08 06:16:27', '2025-01-08 06:16:27'),
(3, 'Branch Manager', 'admin', '2025-01-08 07:22:55', '2025-01-08 07:22:55'),
(4, 'Kitchen Manager', 'admin', '2025-01-21 09:47:21', '2025-01-21 09:47:21');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(5, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(93, 1),
(94, 1),
(95, 1),
(96, 1),
(97, 1),
(98, 1),
(99, 1),
(100, 1),
(101, 1),
(102, 1),
(103, 1),
(104, 1),
(105, 1),
(106, 1),
(107, 1),
(108, 1),
(109, 1),
(110, 1),
(111, 1),
(112, 1),
(113, 1),
(114, 1),
(115, 1),
(116, 1),
(117, 1),
(118, 1),
(119, 1),
(120, 1),
(125, 1),
(126, 1),
(127, 1),
(128, 1),
(129, 1),
(130, 1),
(131, 1),
(132, 1),
(133, 1),
(134, 1),
(135, 1),
(136, 1),
(137, 1),
(138, 1),
(139, 1),
(140, 1),
(153, 1),
(156, 1),
(157, 1),
(158, 1),
(159, 1),
(160, 1),
(161, 1),
(162, 1),
(163, 1),
(164, 1),
(165, 1),
(166, 1),
(167, 1),
(168, 1),
(169, 1),
(170, 1),
(171, 1),
(172, 1),
(173, 1),
(174, 1),
(175, 1),
(176, 1),
(197, 1),
(198, 1),
(199, 1),
(200, 1),
(205, 1),
(206, 1),
(207, 1),
(208, 1),
(217, 1),
(218, 1),
(219, 1),
(220, 1),
(221, 1),
(222, 1),
(223, 1),
(224, 1),
(225, 1),
(226, 1),
(227, 1),
(228, 1),
(229, 1),
(230, 1),
(231, 1),
(232, 1),
(233, 1),
(234, 1),
(235, 1),
(236, 1),
(237, 1),
(238, 1),
(239, 1),
(240, 1),
(241, 1),
(242, 1),
(243, 1),
(244, 1),
(285, 1),
(286, 1),
(287, 1),
(288, 1),
(289, 1),
(290, 1),
(291, 1),
(292, 1),
(293, 1),
(294, 1),
(295, 1),
(296, 1),
(297, 1),
(298, 1),
(299, 1),
(300, 1),
(301, 1),
(302, 1),
(303, 1),
(304, 1),
(305, 1),
(306, 1),
(307, 1),
(308, 1),
(309, 1),
(310, 1),
(311, 1),
(312, 1),
(313, 1),
(314, 1),
(315, 1),
(316, 1),
(317, 1),
(318, 1),
(319, 1),
(320, 1),
(329, 1),
(330, 1),
(331, 1),
(332, 1),
(333, 1),
(334, 1),
(335, 1),
(336, 1),
(337, 1),
(339, 1),
(340, 1),
(341, 1),
(342, 1),
(343, 1),
(344, 1),
(357, 1),
(358, 1),
(359, 1),
(360, 1),
(377, 1),
(378, 1),
(379, 1),
(380, 1),
(381, 1),
(382, 1),
(383, 1),
(384, 1),
(389, 1),
(390, 1),
(391, 1),
(392, 1),
(393, 1),
(394, 1),
(395, 1),
(396, 1),
(397, 1),
(398, 1),
(399, 1),
(400, 1),
(405, 1),
(406, 1),
(407, 1),
(408, 1),
(409, 1),
(410, 1),
(411, 1),
(412, 1),
(413, 1),
(414, 1),
(415, 1),
(416, 1),
(417, 1),
(418, 1),
(419, 1),
(420, 1),
(421, 1),
(422, 1),
(423, 1),
(424, 1),
(425, 1),
(426, 1),
(427, 1),
(428, 1),
(429, 1),
(430, 1),
(431, 1),
(432, 1),
(433, 1),
(434, 1),
(435, 1),
(436, 1),
(437, 1),
(440, 1),
(441, 1),
(442, 1),
(443, 1),
(444, 1),
(445, 1),
(446, 1),
(447, 1),
(448, 1),
(449, 1),
(450, 1),
(451, 1),
(452, 1),
(453, 1),
(454, 1),
(455, 1),
(456, 1),
(457, 1),
(458, 1),
(459, 1),
(460, 1),
(461, 1),
(462, 1),
(463, 1),
(464, 1),
(465, 1),
(466, 1),
(467, 1),
(468, 1),
(469, 1),
(470, 1),
(471, 1),
(472, 1),
(473, 1),
(474, 1),
(475, 1),
(476, 1),
(477, 1),
(478, 1),
(479, 1),
(480, 1),
(481, 1),
(482, 1),
(483, 1),
(484, 1),
(485, 1),
(486, 1),
(487, 1),
(488, 1),
(489, 1),
(490, 1),
(1, 2),
(5, 2),
(21, 2),
(22, 2),
(23, 2),
(24, 2),
(25, 2),
(26, 2),
(27, 2),
(28, 2),
(29, 2),
(30, 2),
(31, 2),
(32, 2),
(33, 2),
(34, 2),
(35, 2),
(36, 2),
(37, 2),
(38, 2),
(39, 2),
(40, 2),
(49, 2),
(50, 2),
(51, 2),
(52, 2),
(53, 2),
(54, 2),
(55, 2),
(56, 2),
(57, 2),
(58, 2),
(59, 2),
(60, 2),
(61, 2),
(62, 2),
(63, 2),
(64, 2),
(65, 2),
(66, 2),
(67, 2),
(68, 2),
(69, 2),
(70, 2),
(71, 2),
(72, 2),
(73, 2),
(74, 2),
(75, 2),
(76, 2),
(93, 2),
(94, 2),
(95, 2),
(96, 2),
(97, 2),
(98, 2),
(99, 2),
(100, 2),
(101, 2),
(102, 2),
(103, 2),
(104, 2),
(105, 2),
(106, 2),
(107, 2),
(108, 2),
(109, 2),
(110, 2),
(111, 2),
(112, 2),
(113, 2),
(114, 2),
(115, 2),
(116, 2),
(117, 2),
(118, 2),
(119, 2),
(120, 2),
(125, 2),
(126, 2),
(127, 2),
(128, 2),
(129, 2),
(130, 2),
(131, 2),
(132, 2),
(133, 2),
(134, 2),
(135, 2),
(136, 2),
(137, 2),
(138, 2),
(139, 2),
(140, 2),
(153, 2),
(156, 2),
(157, 2),
(158, 2),
(159, 2),
(160, 2),
(161, 2),
(162, 2),
(163, 2),
(164, 2),
(165, 2),
(166, 2),
(167, 2),
(168, 2),
(169, 2),
(170, 2),
(171, 2),
(172, 2),
(173, 2),
(174, 2),
(175, 2),
(176, 2),
(197, 2),
(198, 2),
(199, 2),
(200, 2),
(205, 2),
(206, 2),
(207, 2),
(208, 2),
(217, 2),
(218, 2),
(219, 2),
(220, 2),
(221, 2),
(222, 2),
(223, 2),
(224, 2),
(225, 2),
(226, 2),
(227, 2),
(228, 2),
(229, 2),
(230, 2),
(231, 2),
(232, 2),
(233, 2),
(234, 2),
(235, 2),
(236, 2),
(237, 2),
(238, 2),
(239, 2),
(240, 2),
(241, 2),
(242, 2),
(243, 2),
(244, 2),
(269, 2),
(270, 2),
(271, 2),
(272, 2),
(285, 2),
(286, 2),
(287, 2),
(288, 2),
(289, 2),
(290, 2),
(291, 2),
(292, 2),
(293, 2),
(294, 2),
(295, 2),
(296, 2),
(297, 2),
(298, 2),
(299, 2),
(300, 2),
(301, 2),
(302, 2),
(303, 2),
(304, 2),
(305, 2),
(306, 2),
(307, 2),
(308, 2),
(309, 2),
(310, 2),
(311, 2),
(312, 2),
(313, 2),
(314, 2),
(315, 2),
(316, 2),
(317, 2),
(318, 2),
(319, 2),
(320, 2),
(329, 2),
(330, 2),
(331, 2),
(332, 2),
(333, 2),
(334, 2),
(335, 2),
(336, 2),
(337, 2),
(338, 2),
(339, 2),
(340, 2),
(341, 2),
(342, 2),
(343, 2),
(344, 2),
(357, 2),
(358, 2),
(359, 2),
(360, 2),
(377, 2),
(378, 2),
(379, 2),
(380, 2),
(381, 2),
(382, 2),
(383, 2),
(384, 2),
(389, 2),
(390, 2),
(391, 2),
(392, 2),
(393, 2),
(394, 2),
(395, 2),
(396, 2),
(397, 2),
(398, 2),
(399, 2),
(400, 2),
(405, 2),
(406, 2),
(407, 2),
(408, 2),
(409, 2),
(410, 2),
(411, 2),
(412, 2),
(413, 2),
(414, 2),
(415, 2),
(416, 2),
(417, 2),
(418, 2),
(419, 2),
(420, 2),
(421, 2),
(422, 2),
(423, 2),
(424, 2),
(425, 2),
(426, 2),
(427, 2),
(428, 2),
(429, 2),
(430, 2),
(431, 2),
(432, 2),
(433, 2),
(434, 2),
(435, 2),
(436, 2),
(437, 2),
(440, 2),
(441, 2),
(442, 2),
(443, 2),
(444, 2),
(445, 2),
(446, 2),
(447, 2),
(448, 2),
(449, 2),
(450, 2),
(451, 2),
(452, 2),
(453, 2),
(454, 2),
(455, 2),
(456, 2),
(457, 2),
(458, 2),
(459, 2),
(460, 2),
(461, 2),
(462, 2),
(463, 2),
(464, 2),
(465, 2),
(466, 2),
(467, 2),
(468, 2),
(469, 2),
(470, 2),
(471, 2),
(472, 2),
(473, 2),
(474, 2),
(475, 2),
(476, 2),
(477, 2),
(478, 2),
(479, 2),
(480, 2),
(481, 2),
(482, 2),
(483, 2),
(484, 2),
(485, 2),
(486, 2),
(487, 2),
(488, 2),
(489, 2),
(490, 2),
(491, 2),
(492, 2),
(493, 2),
(494, 2),
(1, 3),
(21, 3),
(23, 3),
(25, 3),
(26, 3),
(27, 3),
(28, 3),
(29, 3),
(30, 3),
(31, 3),
(32, 3),
(33, 3),
(34, 3),
(35, 3),
(36, 3),
(69, 3),
(70, 3),
(71, 3),
(72, 3),
(169, 3),
(170, 3),
(171, 3),
(172, 3),
(205, 3),
(206, 3),
(207, 3),
(208, 3),
(217, 3),
(218, 3),
(219, 3),
(220, 3),
(221, 3),
(222, 3),
(223, 3),
(224, 3),
(225, 3),
(226, 3),
(227, 3),
(228, 3),
(229, 3),
(230, 3),
(231, 3),
(232, 3),
(233, 3),
(234, 3),
(235, 3),
(237, 3),
(238, 3),
(239, 3),
(240, 3),
(241, 3),
(242, 3),
(243, 3),
(244, 3),
(329, 3),
(330, 3),
(331, 3),
(332, 3),
(333, 3),
(334, 3),
(335, 3),
(336, 3),
(397, 3),
(398, 3),
(399, 3),
(400, 3),
(425, 3),
(426, 3),
(427, 3),
(428, 3),
(429, 3),
(434, 3),
(435, 3),
(436, 3),
(437, 3),
(440, 3),
(441, 3),
(442, 3),
(443, 3),
(444, 3),
(445, 3),
(446, 3),
(447, 3),
(448, 3),
(457, 3),
(458, 3),
(459, 3),
(460, 3),
(461, 3),
(462, 3),
(463, 3),
(464, 3),
(465, 3),
(466, 3),
(467, 3),
(471, 3),
(472, 3),
(473, 3),
(474, 3),
(476, 3),
(477, 3),
(1, 4),
(33, 4),
(34, 4),
(35, 4),
(36, 4),
(49, 4),
(50, 4),
(51, 4),
(52, 4),
(73, 4),
(74, 4),
(75, 4),
(76, 4),
(101, 4),
(102, 4),
(103, 4),
(104, 4),
(105, 4),
(106, 4),
(107, 4),
(108, 4),
(109, 4),
(110, 4),
(111, 4),
(112, 4),
(113, 4),
(114, 4),
(115, 4),
(116, 4),
(329, 4),
(330, 4),
(331, 4),
(332, 4),
(333, 4),
(334, 4),
(335, 4),
(336, 4),
(482, 4);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `stock_transfer_method` enum('First In First Out','Last In First Out') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'First In First Out',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tax_application` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:tax not included, 1:tax included',
  `tax_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `pricing_method` enum('original_price','avg_price','new_price') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'avg_price',
  `service_fees` decimal(10,2) NOT NULL DEFAULT '0.00',
  `coupon_application` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:before tax, 1:after tax',
  `store_use` tinyint(1) NOT NULL DEFAULT '0',
  `withdrawal_store` int DEFAULT '1' COMMENT '1 manual, 2 automatic',
  `reservation_time` int DEFAULT '0',
  `reservation_time_type` int DEFAULT '1' COMMENT '1 for moment, 2 for day',
  `hotline` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_cancellation` int DEFAULT NULL,
  `closing_cashier` int NOT NULL DEFAULT '0' COMMENT '0 not closing, 1 closing',
  `transgression_leave_type` int DEFAULT '1' COMMENT '1 for day, 2 for money',
  `transgression_leave` int DEFAULT '0',
  `delivery_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `stock_transfer_method`, `created_at`, `updated_at`, `tax_application`, `tax_percentage`, `pricing_method`, `service_fees`, `coupon_application`, `store_use`, `withdrawal_store`, `reservation_time`, `reservation_time_type`, `hotline`, `time_cancellation`, `closing_cashier`, `transgression_leave_type`, `transgression_leave`, `delivery_time`) VALUES
(1, 'First In First Out', NULL, NULL, 0, 14.00, 'avg_price', 14.00, 0, 0, 1, 0, 1, NULL, NULL, 0, 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shelves`
--

CREATE TABLE `shelves` (
  `id` bigint UNSIGNED NOT NULL,
  `division_id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shift_details`
--

CREATE TABLE `shift_details` (
  `id` bigint UNSIGNED NOT NULL,
  `shift_id` bigint UNSIGNED NOT NULL,
  `timetable_id` bigint UNSIGNED NOT NULL,
  `day_index` tinyint NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sizes`
--

CREATE TABLE `sizes` (
  `id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sizes`
--

INSERT INTO `sizes` (`id`, `name_ar`, `name_en`, `category_id`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `deleted_by`, `modified_by`) VALUES
(7, 'صغير جدا', 'XS', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'صغير', 'S', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'متوسط', 'M', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'كبير', 'L', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'كبير جدا', 'XL', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'كبير جدا جدا', 'XXL', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(13, '36', '36', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(14, '37', '37', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(15, '38', '38', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(16, '39', '39', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(17, '40', '40', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(18, '41', '41', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(19, '41', '41', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(20, '43', '43', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(21, '44', '44', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(22, '45', '45', 1, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dish_id` bigint UNSIGNED DEFAULT NULL,
  `offer_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `discount_id` bigint UNSIGNED DEFAULT NULL,
  `flag` enum('dish','offer','discount') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dish'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `name_ar`, `name_en`, `description_ar`, `description_en`, `image`, `dish_id`, `offer_id`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `discount_id`, `flag`) VALUES
('160c11d2-ba90-4f2e-b384-14d3f5064aca', 'new', 'new', 'aa', 'aa', 'https://erpsystem.testdomain100.online/images/sliders/1736949647.png', 43, NULL, 9, NULL, NULL, '2025-01-15 15:00:47', '2025-01-15 15:01:08', '2025-01-15 15:01:08', NULL, 'dish'),
('4555881d-0d92-45fd-bb6f-0bd234ac54c5', 'مجبوس لحم عربي ضاني طازج', 'مجبوس لحم عربي ضاني طازج', 'عيش مع لحم ضاني يقدم مع معبوج أخضر ومعبوج أحمر ومرق باميه او دقوس', 'عيش مع لحم ضاني يقدم مع معبوج أخضر ومعبوج أحمر ومرق باميه او دقوس', 'https://erpsystem.testdomain100.online/images/sliders/4555881d-0d92-45fd-bb6f-0bd234ac54c51737451767.jpg', 46, NULL, 9, 9, NULL, '2025-01-16 09:56:32', '2025-01-21 10:29:27', NULL, NULL, 'dish'),
('762ed4bd-60db-4ed8-a74c-6d07790eea0d', 'مجبوس دجاج', 'مجبوس دجاج', 'عيش مع نصف دجاجه يقدم مع معبوج أخضر ومعبوج أحمر ودقوس او مرق باميه', 'عيش مع نصف دجاجه يقدم مع معبوج أخضر ومعبوج أحمر ودقوس او مرق باميه', 'https://erpsystem.testdomain100.online/images/sliders/762ed4bd-60db-4ed8-a74c-6d07790eea0d1737451719.jpg', 46, NULL, 1, 9, NULL, '2025-01-08 12:18:15', '2025-01-21 10:28:39', NULL, NULL, 'dish');

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_kitchen` tinyint(1) NOT NULL DEFAULT '0',
  `is_freeze` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `store_categories`
--

CREATE TABLE `store_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `store_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `store_transactions`
--

CREATE TABLE `store_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `store_id` bigint UNSIGNED DEFAULT NULL,
  `type` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1 for outgoing, 2 for incoming',
  `to_type` enum('1','2','3','4') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1 from store, 2 from client, 3 from vender, 4 from employee',
  `to_id` int NOT NULL COMMENT 'id for to_type column from users and vendors and stores',
  `date` date NOT NULL,
  `total` int NOT NULL DEFAULT '0',
  `total_price` decimal(8,2) DEFAULT '0.00',
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `invoice_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_id` int DEFAULT '0' COMMENT 'order id, purchase id',
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `store_transaction_details`
--

CREATE TABLE `store_transaction_details` (
  `id` bigint UNSIGNED NOT NULL,
  `store_transaction_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `product_unit_id` bigint UNSIGNED DEFAULT NULL,
  `product_size_id` bigint UNSIGNED DEFAULT NULL,
  `product_color_id` bigint UNSIGNED DEFAULT NULL,
  `country_id` bigint UNSIGNED NOT NULL COMMENT 'to get currency id',
  `expirt_date` date DEFAULT NULL,
  `count` double(15,2) NOT NULL DEFAULT '0.00',
  `price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `total_price` double(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `id` bigint UNSIGNED NOT NULL,
  `floor_id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `table_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int NOT NULL DEFAULT '1',
  `status` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1 for available, 2 occupied, 3 reserved',
  `type` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1 on door, 2 out door',
  `smoking` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1 smokin, 2 not smokin',
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `floor_partition_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `table_reservations`
--

CREATE TABLE `table_reservations` (
  `id` bigint UNSIGNED NOT NULL,
  `table_id` bigint UNSIGNED DEFAULT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time_from` time DEFAULT NULL,
  `time_to` time DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1' COMMENT '1 for pendding, 2 for existing, 3 for empty',
  `confirmed` int NOT NULL DEFAULT '1' COMMENT '1 for pendding, 2 for confirm, 3 for reject',
  `confirmed_date` date DEFAULT NULL,
  `confirmed_time` time DEFAULT NULL,
  `confirmed_by` bigint UNSIGNED DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tax_kinds`
--

CREATE TABLE `tax_kinds` (
  `id` int NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `desc_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `desc_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `tax_kinds`
--

INSERT INTO `tax_kinds` (`id`, `code`, `desc_en`, `desc_ar`) VALUES
(1, 'T1', 'Value added tax', 'ضريبه القيمه المضافه'),
(2, 'T2', 'Table tax (percentage)', 'ضريبه الجدول (نسبيه)'),
(3, 'T3', 'Table tax (Fixed Amount)', 'ضريبه الجدول (قطعيه)'),
(4, 'T4', 'Withholding tax (WHT)', 'الخصم تحت حساب الضريبه'),
(5, 'T5', 'Stamping tax (percentage)', 'ضريبه الدمغه (نسبيه)'),
(6, 'T6', 'Stamping Tax (amount)', 'ضريبه الدمغه (قطعيه بمقدار ثابت )'),
(7, 'T7', 'Entertainment tax', 'ضريبة الملاهى'),
(8, 'T8', 'Resource development fee', 'رسم تنميه الموارد'),
(9, 'T9', 'Table tax (percentage)', 'رسم خدمة'),
(10, 'T10', 'Municipality Fees', 'رسم المحليات'),
(11, 'T11', 'Medical insurance fee', 'رسم التامين الصحى'),
(12, 'T12', 'Other fees', 'رسوم أخري'),
(13, 'T13', 'Stamping tax (percentage)', 'ضريبه الدمغه (نسبيه)'),
(14, 'T14', 'Stamping Tax (amount)', 'ضريبه الدمغه (قطعيه بمقدار ثابت )'),
(15, 'T15', 'Entertainment tax', 'ضريبة الملاهى'),
(16, 'T16', 'Resource development fee', 'رسم تنميه الموارد'),
(17, 'T17', 'Table tax (percentage)', 'رسم خدمة'),
(18, 'T18', 'Municipality Fees', 'رسم المحليات'),
(19, 'T19', 'Medical insurance fee', 'رسم التامين الصحى'),
(20, 'T20', 'Other fees', 'رسوم أخرى');

-- --------------------------------------------------------

--
-- Table structure for table `tax_sub_kinds`
--

CREATE TABLE `tax_sub_kinds` (
  `id` int NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `desc_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `desc_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `tax_kind_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `default` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `tax_sub_kinds`
--

INSERT INTO `tax_sub_kinds` (`id`, `code`, `desc_en`, `desc_ar`, `tax_kind_reference`, `default`) VALUES
(1, 'V001', 'Export', 'تصدير للخارج', 'T1', 0),
(2, 'V002', 'Export to free areas and other areas', 'تصدير مناطق حرة وأخرى', 'T1', 0),
(3, 'V003', 'Exempted good or service', 'سلعة أو خدمة معفاة', 'T1', 0),
(4, 'V004', 'A non-taxable good or service', 'سلعة أو خدمة غير خاضعة للضريبة', 'T1', 0),
(5, 'V005', 'Exemptions for diplomats, consulates and embassies', 'إعفاءات دبلوماسين والقنصليات والسفارات', 'T1', 0),
(6, 'V006', 'Defence and National security Exemptions', 'إعفاءات الدفاع والأمن القومى', 'T1', 0),
(7, 'V007', 'Agreements exemptions', 'إعفاءات اتفاقيات', 'T1', 0),
(8, 'V008', 'Special Exemptios and other reasons', 'إعفاءات خاصة و أخرى', 'T1', 0),
(9, 'V009', 'General Item sales', 'سلع عامة', 'T1', 1),
(10, 'V010', 'Other Rates', 'نسب ضريبة أخرى', 'T1', 0),
(11, 'Tbl01', 'Table tax (percentage)', 'ضريبه الجدول (نسبيه)', 'T2', 0),
(12, 'Tbl02', 'Table tax (Fixed Amount)', 'ضريبه الجدول (النوعية)', 'T3', 0),
(13, 'W001', 'Contracting', 'المقاولات', 'T4', 0),
(14, 'W002', 'Supplies', 'التوريدات', 'T4', 0),
(15, 'W003', 'Purachases', 'المشتريات', 'T4', 0),
(16, 'W004', 'Services', 'الخدمات', 'T4', 1),
(17, 'W005', 'Sumspaid by the cooperative societies for car transportation to their members', 'المبالغالتي تدفعها الجميعات التعاونية للنقل بالسيارات لاعضائها', 'T4', 0),
(18, 'W006', 'Commissionagency & brokerage', 'الوكالةبالعمولة والسمسرة', 'T4', 0),
(19, 'W007', 'Discounts& grants & additional exceptional incentives granted by smoke &cement companies', 'الخصوماتوالمنح والحوافز الاستثنائية ةالاضافية التي تمنحها شركات الدخان والاسمنت ', 'T4', 0),
(20, 'W008', 'Alldiscounts & grants & commissions granted by petroleum &telecommunications & other companies', 'جميعالخصومات والمنح والعمولات  التيتمنحها  شركات البترول والاتصالات ...وغيرها من الشركات المخاطبة بنظام الخصم', 'T4', 0),
(21, 'W009', 'Supporting export subsidies', 'مساندة دعم الصادرات التي يمنحها صندوق تنمية الصادرات ', 'T4', 0),
(22, 'W010', 'Professional fees', 'اتعاب مهنية', 'T4', 0),
(23, 'W011', 'Commission & brokerage _A_57', 'العمولة والسمسرة _م_57', 'T4', 0),
(24, 'W012', 'Hospitals collecting from doctors', 'تحصيل المستشفيات من الاطباء', 'T4', 0),
(25, 'W013', 'Royalties', 'الاتاوات', 'T4', 0),
(26, 'W014', 'Customs clearance', 'تخليص جمركي ', 'T4', 0),
(27, 'W015', 'Exemption', 'أعفاء', 'T4', 0),
(28, 'W016', 'advance payments', 'دفعات مقدمه', 'T4', 0),
(29, 'ST01', 'Stamping tax (percentage)', 'ضريبه الدمغه (نسبيه)', 'T5', 0),
(30, 'ST02', 'Stamping Tax (amount)', 'ضريبه الدمغه (قطعيه بمقدار ثابت)', 'T6', 0),
(31, 'Ent01', 'Entertainment tax (rate)', 'ضريبة الملاهى (نسبة)', 'T7', 1),
(32, 'Ent02', 'Entertainment tax (amount)', 'ضريبة الملاهى (قطعية)', 'T7', 0),
(33, 'RD01', 'Resource development fee (rate)', 'رسم تنميه الموارد (نسبة)', 'T8', 1),
(34, 'RD02', 'Resource development fee (amount)', 'رسم تنميه الموارد (قطعية)', 'T8', 0),
(35, 'SC01', 'Service charges (rate)', 'رسم خدمة (نسبة)', 'T9', 1),
(36, 'SC02', 'Service charges (amount)', 'رسم خدمة (قطعية)', 'T9', 0),
(37, 'Mn01', 'Municipality Fees (rate)', 'رسم المحليات (نسبة)', 'T10', 1),
(38, 'Mn02', 'Municipality Fees (amount)', 'رسم المحليات (قطعية)', 'T10', 0),
(39, 'MI01', 'Medical insurance fee (rate)', 'رسم التامين الصحى (نسبة)', 'T11', 1),
(40, 'MI02', 'Medical insurance fee (amount)', 'رسم التامين الصحى (قطعية)', 'T11', 0),
(41, 'OF01', 'Other fees (rate)', 'رسوم أخرى (نسبة)', 'T12', 1),
(42, 'OF02', 'Other fees (amount)', 'رسوم أخرى (قطعية)', 'T12', 0),
(43, 'ST03', 'Stamping tax (percentage)', 'ضريبه الدمغه (نسبيه)', 'T13', 0),
(44, 'ST04', 'Stamping Tax (amount)', 'ضريبه الدمغه (قطعيه بمقدار ثابت)', 'T14', 0),
(45, 'Ent03', 'Entertainment tax (rate)', 'ضريبة الملاهى (نسبة)', 'T15', 0),
(46, 'Ent04', 'Entertainment tax (amount)', 'ضريبة الملاهى (قطعية)', 'T15', 0),
(47, 'RD03', 'Resource development fee (rate)', 'رسم تنميه الموارد (نسبة)', 'T16', 0),
(48, 'RD04', 'Resource development fee (amount)', 'رسم تنميه الموارد (قطعية)', 'T16', 0),
(49, 'SC03', 'Service charges (rate)', 'رسم خدمة (نسبة)', 'T17', 0),
(50, 'SC04', 'Service charges (amount)', 'رسم خدمة (قطعية)', 'T17', 0),
(51, 'Mn03', 'Municipality Fees (rate)', 'رسم المحليات (نسبة)', 'T18', 0),
(52, 'Mn04', 'Municipality Fees (amount)', 'رسم المحليات (قطعية)', 'T18', 0),
(53, 'MI03', 'Medical insurance fee (rate)', 'رسم التامين الصحى (نسبة)', 'T19', 0),
(54, 'MI04', 'Medical insurance fee (amount)', 'رسم التامين الصحى (قطعية)', 'T19', 0),
(55, 'OF03', 'Other fees (rate)', 'رسوم أخرى (نسبة)', 'T20', 0),
(56, 'OF04', 'Other fees (amount)', 'رسوم أخرى (قطعية)', 'T20', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `data` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `created_at`, `updated_at`, `data`) VALUES
('foo', '2024-11-18 11:37:10', '2024-11-18 11:37:10', '{\"created_at\": \"2024-11-18 13:37:10\", \"updated_at\": \"2024-11-18 13:37:10\", \"tenancy_db_name\": \"tenantfoo\"}'),
('new', '2024-11-18 11:37:58', '2024-11-18 11:37:58', '{\"created_at\": \"2024-11-18 13:37:58\", \"updated_at\": \"2024-11-18 13:37:58\", \"tenancy_db_name\": \"tenantnew\"}');

-- --------------------------------------------------------

--
-- Table structure for table `terms_and_conditions`
--

CREATE TABLE `terms_and_conditions` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `terms_and_conditions`
--

INSERT INTO `terms_and_conditions` (`id`, `name_ar`, `name_en`, `description_ar`, `description_en`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `active`) VALUES
('4d9b8d02-f4c6-49b0-b3c9-abbac83cb382', 'شرط 2', 'Term 2', '<p>وصف شرط 2</p>', '<p>Term Description 2</p>', 1, 1, NULL, '2024-12-22 11:03:49', '2024-12-30 05:46:19', NULL, 1),
('5709b1ba-e3f8-4a9b-8ffc-df42cb04f1b5', 'شرط 1', 'Term 1', '<p>وصف شرط 1</p>', '<p>Term Description 1</p>', 1, 1, NULL, '2024-12-18 09:41:56', '2024-12-30 05:46:28', NULL, 1),
('aefb76ed-81cc-4336-b820-3b3f17ffc699', 'شرط 3', 'Term 3', '<p>وصف</p>', '<p>Description</p>', 1, 9, NULL, '2024-12-24 08:30:30', '2025-01-15 09:16:27', NULL, 0),
('b5f365f1-2e49-47b0-94b0-ff5354074ee0', 'شرط 2', 'Term 2', '<p>وصف</p>', '<p>description</p>', 1, 1, NULL, '2024-12-22 11:01:38', '2024-12-22 11:03:14', '2024-12-22 11:03:14', 1);

-- --------------------------------------------------------

--
-- Table structure for table `timetables`
--

CREATE TABLE `timetables` (
  `id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `on_duty_time` time NOT NULL,
  `off_duty_time` time NOT NULL,
  `start_sign_in` time NOT NULL,
  `end_sign_in` time NOT NULL,
  `start_sign_out` time NOT NULL,
  `end_sign_out` time NOT NULL,
  `lateness_grace_period` int NOT NULL DEFAULT '0',
  `start_late_time_option` enum('after_duty_time_grace_period','after_duty_time','from_duty_time') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'after_duty_time_grace_period',
  `cross_day` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint UNSIGNED NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `modify_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name_ar`, `name_en`, `created_by`, `deleted_by`, `modify_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(16, 'ك ج', 'kg', 1, NULL, NULL, '2025-01-08 10:00:17', '2025-01-08 10:00:17', NULL),
(17, 'منتج 1', 'Product 1', 9, NULL, NULL, '2025-01-09 08:05:39', '2025-01-09 08:05:45', '2025-01-09 08:05:45');

-- --------------------------------------------------------

--
-- Table structure for table `unit_types`
--

CREATE TABLE `unit_types` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `unit_types`
--

INSERT INTO `unit_types` (`id`, `code`, `desc_en`, `desc_ar`, `created_at`, `updated_at`) VALUES
(1, '2Z', 'Millivolt ( mV )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(2, '4K', 'Milliampere ( mA )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(3, '4O', 'Microfarad ( microF )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(4, 'A87', 'Gigaohm ( GOhm )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(5, 'A93', 'Gram/Cubic meter ( g/m3 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(6, 'A94', 'Gram/cubic centimeter ( g/cm3 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(7, 'AMP', 'Ampere ( A )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(8, 'ANN', 'Years ( yr )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(9, 'B22', 'Kiloampere ( kA )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(10, 'B49', 'Kiloohm ( kOhm )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(11, 'B75', 'Megohm ( MOhm )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(12, 'B78', 'Megavolt ( MV )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(13, 'B84', 'Microampere ( microA )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(14, 'BAR', 'bar ( bar )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(15, 'BBL', 'Barrel (oil 42 gal.)', 'برميل', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(16, 'BG', 'Bag ( Bag )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(17, 'BO', 'Bottle ( Bt. )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(18, 'BOX', 'Box', 'صندوق', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(19, 'C10', 'Millifarad ( mF )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(20, 'C39', 'Nanoampere ( nA )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(21, 'C41', 'Nanofarad ( nF )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(22, 'C45', 'Nanometer ( nm )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(23, 'C62', 'Activity unit ( AU )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(24, 'CA', 'Canister ( Can )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(25, 'CMK', 'Square centimeter ( cm2 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(26, 'CMQ', 'Cubic centimeter ( cm3 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(27, 'CMT', 'Centimeter ( cm )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(28, 'CS', 'Case ( Case )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(29, 'CT', 'Carton ( Car )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(30, 'CTL', 'Centiliter ( Cl )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(31, 'D10', 'Siemens per meter ( S/m )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(32, 'D33', 'Tesla ( D )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(33, 'D41', 'Ton/Cubic meter ( t/m3 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(34, 'DAY', 'Days ( d )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(35, 'DMT', 'Decimeter ( dm )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(36, 'DRM', 'DRUM', 'أسطوانة', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(37, 'EA', 'each (ST) ( ST )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(38, 'FAR', 'Farad ( F )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(39, 'FOT', 'Foot ( Foot )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(40, 'FTK', 'Square foot ( ft2 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(41, 'FTQ', 'Cubic foot ( ft3 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(42, 'G42', 'Microsiemens per centimeter ( microS/cm )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(43, 'GL', 'Gram/liter ( g/l )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(44, 'GLL', 'gallon ( gal )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(45, 'GM', 'Gram/square meter ( g/m2 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(46, 'GPT', 'Gallon per thousand', 'جالون/الف', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(47, 'GRM', 'Gram ( g )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(48, 'H63', 'Milligram/Square centimeter ( mg/cm2 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(49, 'HHP', 'Hydraulic Horse Power', 'قوة حصان هيدروليكي', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(50, 'HLT', 'Hectoliter ( hl )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(51, 'HTZ', 'Hertz (1/second) ( Hz )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(52, 'HUR', 'Hours ( hrs )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(53, 'IE', 'Number of Persons ( PRS )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(54, 'INH', 'Inch ( “” )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(55, 'INK', 'Square inch ( Inch2 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(56, 'JOB', 'JOB', 'وظيفة', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(57, 'KGM', 'Kilogram ( KG )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(58, 'KHZ', 'Kilohertz ( kHz )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(59, 'KMH', 'Kilometer/hour ( km/h )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(60, 'KMK', 'Square kilometer ( km2 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(61, 'KMQ', 'Kilogram/cubic meter ( kg/m3 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(62, 'KMT', 'Kilometer ( km )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(63, 'KSM', 'Kilogram/Square meter ( kg/m2 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(64, 'KVT', 'Kilovolt ( kV )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(65, 'KWT', 'Kilowatt ( KW )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(66, 'LB', 'pounds ', 'رطل', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(67, 'LTR', 'Liter ( l )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(68, 'LVL', 'Level', 'مستوي', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(69, 'M', 'Meter ( m )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(70, 'MAN', 'Man', 'رجل', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(71, 'MAW', 'Megawatt ( VA )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(72, 'MGM', 'Milligram ( mg )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(73, 'MHZ', 'Megahertz ( MHz )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(74, 'MIN', 'Minute ( min )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(75, 'MMK', 'Square millimeter ( mm2 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(76, 'MMQ', 'Cubic millimeter ( mm3 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(77, 'MMT', 'Millimeter ( mm )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(78, 'MON', 'Months ( Months )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(79, 'MTK', 'Square meter ( m2 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(80, 'MTQ', 'Cubic meter ( m3 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(81, 'OHM', 'Ohm ( Ohm )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(82, 'ONZ', 'Ounce ( oz )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(83, 'PAL', 'Pascal ( Pa )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(84, 'PF', 'Pallet ( PAL )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(85, 'PK', 'Pack ( PAK )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(86, 'SK', 'Sack', 'كيس', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(87, 'SMI', 'Mile ( mile )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(88, 'ST', 'Sheet', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(89, 'TNE', 'Tonne ( t )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(90, 'TON', 'Ton (metric)', 'طن (متري)', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(91, 'VLT', 'Volt ( V )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(92, 'WEE', 'Weeks ( Weeks )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(93, 'WTT', 'Watt ( W )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(94, 'X03', 'Meter/Hour ( m/h )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(95, 'YDQ', 'Cubic yard ( yd3 )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(96, 'YRD', 'Yards ( yd )', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(97, 'NMP', 'Number of packs', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(98, '5I', 'Standard cubic foot', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(99, 'AE', 'Ampere per metre', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(100, 'B4', 'Barrel, Imperial', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(101, 'BB', 'Base box', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(102, 'BD', 'Board', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(103, 'BE', 'Bundle', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(104, 'BK', 'Basket', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(105, 'BL', 'Bale', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(106, 'CH', 'Container', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(107, 'CR', 'Crate', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(108, 'DAA', 'Decare', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(109, 'DTN', 'Decitonne', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(110, 'DZN', 'Dozen', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(111, 'FP', 'Pound per square foot', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(112, 'HMT', 'Hectometre', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(113, 'INQ', 'Cubic inch', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(114, 'KG', 'Keg', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(115, 'KTM', 'Kilometre', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(116, 'LO', 'Lot [unit of procurement]', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(117, 'MLT', 'Millilitre', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(118, 'MT', 'Mat', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(119, 'NA', 'Milligram per kilogram', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(120, 'NAR', 'Number of articles', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(121, 'NC', 'Car', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(122, 'NE', 'Net litre', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(123, 'NPL', 'Number of parcels', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(124, 'NV', 'Vehicle', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(125, 'PA', 'Packet', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(126, 'PG', 'Plate', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(127, 'PL', 'Pail', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(128, 'PR', 'Pair', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(129, 'PT', 'Pint (US)', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(130, 'RL', 'Reel', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(131, 'RO', 'Roll', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(132, 'SET', 'Set', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(133, 'STK', 'Stick, Cigarette ', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(134, 'T3', 'Thousand piece', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(135, 'TC', 'Truckload', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(136, 'TK', 'Tank, rectangular', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(137, 'TN', 'Tin', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(138, 'TTS', 'Ten thousand sticks', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(139, 'UC', 'Telecommunication port ', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(140, 'VI', 'Vial', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(141, 'VQ', 'Bulk', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(142, 'YDK', 'Square yard', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04'),
(143, 'Z3', 'Cask', '', '2025-01-29 11:22:04', '2025-01-29 11:22:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rule_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flag` enum('admin','client','unknown','employee') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'client',
  `is_active` int NOT NULL DEFAULT '1',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `google_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `country_id`, `country_code`, `phone`, `code`, `age`, `birth_date`, `rule_id`, `flag`, `is_active`, `remember_token`, `created_at`, `updated_at`, `google_id`, `facebook_id`, `deleted_at`) VALUES
(1, 'admin', 'adminn@adminn.com', NULL, '$2y$10$08SNo6e3TXDbCXRstCHoXuU9rtWvAQ8EBV.vW2a67nDqbAEvmqLQy', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '123123123', NULL, NULL, NULL, NULL, 'admin', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'LocalWork', 'LocalWork@admin.com', NULL, '$2y$10$08SNo6e3TXDbCXRstCHoXuU9rtWvAQ8EBV.vW2a67nDqbAEvmqLQy', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '', '01000416716', NULL, NULL, NULL, NULL, 'admin', 1, NULL, '2024-09-24 09:01:00', '2024-09-24 09:01:00', NULL, NULL, NULL),
(10, 'admin', 'admin@admin.com', NULL, '$2y$10$xjm1ZgJ3lF3C0CWGRTlu3ubVJR57wb9v3DmJBT1xL.OUFqlvusIl.', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '', '123123123', NULL, NULL, NULL, NULL, 'admin', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'unkown', 'unknown@unknown.com', NULL, '$2y$10$KRuT/q/luS8evgCtmdviueUb57AuKLMyU8MulQnMmvm8fGqTDBTT6', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '', '123123125', NULL, NULL, NULL, NULL, 'unknown', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'ISchool user1', 'ischooluser1@gmail.com', NULL, NULL, '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '', 'null', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-11-04 10:19:42', '2024-11-04 10:19:42', '105573525573982136413', NULL, NULL),
(15, 'Wessam Fawzy', 'wessam@admin.com', NULL, '$2y$10$sDRoYFb7/gCGF1ZPJ6I4nO0SyewZdkyKegD4KqhCrqL5KSXcYPzWy', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '1', '0122334455', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-15 09:02:26', '2025-01-01 11:29:58', NULL, NULL, NULL),
(17, 'khlood ibrahim', 'test18@admin.com', NULL, '$2y$10$NfuXjgHkx2c.IGJgd8udAugoyXBYKg4mCa.HgN92UYtwpSa1OfD5W', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '', '01201001817', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-18 08:35:20', '2024-12-18 08:35:20', NULL, NULL, NULL),
(19, 'khlood ibrahim', 'testkhlood12@admin.com', NULL, '$2y$10$UQRjjf6LZ40SC0O/VXIRz.wdP2xEalD8ARA.haYiEsEwDtNp5wbxG', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01201001819', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-18 13:17:57', '2024-12-18 13:19:06', NULL, NULL, NULL),
(20, 'khlood ibrahim', 'test518@admin.com', NULL, '$2y$10$LT5Be53L4BT7R6VVaMZCo.1Bl48aGaAxIJ2d0nqsCixLxMUeexGgG', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '0120', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-18 13:19:10', '2024-12-18 13:19:10', NULL, NULL, NULL),
(21, 'omar hassan', 'omar@gmail.com', NULL, '$2y$10$16u/IjhAQIMfqW.qTvy7KODK6.uSG7XO0Mw.bskiv3e22xZ/F9IlS', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01140', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-18 13:19:54', '2024-12-18 13:19:54', NULL, NULL, NULL),
(22, 'احمد عيد صديق', 'ahmedeid2026@gmail.com', NULL, '$2y$10$f5MAUNqBtlVZ.Es2iQeGh.UBmDGKfZcy.VirupgBDZt192WS.3rgq', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+965', '01091510', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-18 13:45:48', '2024-12-18 13:45:48', NULL, NULL, NULL),
(23, 'khlood ibrahim', 'testkhlood122@admin.com', NULL, '$2y$10$cUOpps/OMD3CtbKBuuh56.6mCXk9u38JgPYLs6OgoxLXWkT0xg/lS', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '012010018199', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-19 05:24:18', '2024-12-19 05:24:18', NULL, NULL, NULL),
(24, 'Ahmed Eid', 'fuygfuyfuy@admin.com', NULL, '$2y$10$p/yfsoO3sr0SOFjmoXTpmuF3d3NfUX5f2FGzp1qT7MC5m9iynA48i', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01091510577', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-19 06:43:16', '2025-01-29 08:46:12', NULL, NULL, '2025-01-29 08:46:12'),
(25, 'Ahmed Eid', 'fuygfuyfuy@admin.comfgdg', NULL, '$2y$10$X8R2oWbJM8F6xJ8hLvQNzOGkVh0/bSPzT5z4RJFwiK/Ay24wO.3OO', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '010915105778', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-19 06:51:22', '2025-01-29 08:45:39', NULL, NULL, '2025-01-29 08:45:39'),
(26, 'omar', 'omar1@gmail.com', NULL, '$2y$10$rBXp8ZnB0OrrtyysUXa5CuU.H/n1gz.FH7vIDMrTjDjDIoArJLYO6', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '0114043', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-19 06:54:02', '2024-12-19 10:11:17', NULL, NULL, NULL),
(28, 'احمد', 'cycle@outlook.com', NULL, '$2y$10$3rh/UlXnIFuovv59eycdtuZlhjZUcFhUwugwWOpg1zLHC59ofwxuy', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01091885524', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-19 07:26:03', '2025-01-29 08:46:17', NULL, NULL, '2025-01-29 08:46:17'),
(29, 'احمد عيد', 'ahm@gmail.com', NULL, '$2y$10$pOk3FN9LXqCg57PGMR5Z3eWWhGMjmWhfVTU8QwzLl/wv1vt..S4HS', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01091510588', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-19 07:27:53', '2025-01-29 08:46:43', NULL, NULL, '2025-01-29 08:46:43'),
(30, 'khlood ibrahim', 'testkhlood15@admin.com', NULL, '$2y$10$gx1B6W4RT2xWifJHGDtu/eEWb3H7ySue4caQ2RwNkrIbrPThVV/YS', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '0125684489', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-19 11:38:23', '2024-12-19 11:38:23', NULL, NULL, NULL),
(31, 'khlood ibrahim', 'testkhlood16@admin.com', NULL, '$2y$10$fZZNwlXjQdnlKeTpj5HO/.Psnbt7316YSIBbgHjqdb74NKphw.APi', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '0125684487', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-19 11:42:00', '2024-12-19 11:42:00', NULL, NULL, NULL),
(32, 'khlood ibrahim', 'testkhlood11@admin.com', NULL, '$2y$10$eXc.jrpvtu.ys/Ghgm1b8u9PRHW8jPvz/5IahK/wg3KMC8sDDvmIK', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01256844866', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-19 11:54:55', '2024-12-19 11:54:55', NULL, NULL, NULL),
(33, 'Wessam Fawzy', 'wessam2047@gmail.com', NULL, '$2y$10$0FZGgRWdva3zUSrvTSurqOe54Gan5l5PU/nmhXtVZ8HoIiz9Ep9w6', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01224303330', NULL, NULL, '2002-10-14', NULL, 'client', 1, NULL, '2024-12-19 12:08:27', '2025-01-21 09:20:06', NULL, NULL, NULL),
(34, 'ahmed', 'ahmed@mail.com', NULL, '$2y$10$2YXg2ey1ezydkz7qeWa40.qw2.YK1oZ9SQ2CmqXkqu9No2ut65Wve', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '222222', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-19 12:59:34', '2024-12-19 12:59:34', NULL, NULL, NULL),
(36, 'Ali', 'ali@mail.com', NULL, '$2y$10$Xd39ZX8.H8CRbPzhaSPjYuBi2Ga8eJwH7.qUA6dNHqePgV91nCitC', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '3216549870', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-22 09:28:23', '2024-12-22 09:28:23', NULL, NULL, NULL),
(38, 'omar', 'omar123@gmail.com', NULL, '$2y$10$wdeNCi/t0KZNWgaWGtd9Q.cNTAcdOedsGzoCHTEwzEX4tAw1cUlp6', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '011404345', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-25 07:25:13', '2024-12-25 07:25:13', NULL, NULL, NULL),
(39, 'omar', 'omar1233@gmail.com', NULL, '$2y$10$6KbzcBw19CRQ9wpTrMXdMu18OgUwiobI50Bx0sD/3K0dzLScuTyfG', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '0114043455', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2024-12-25 07:25:36', '2024-12-25 07:25:36', NULL, NULL, NULL),
(40, 'جديد', 'sh@client.com', NULL, '$2y$10$C12xvfjNze7svsl.Yy/o1Ob1YbEFFnpydfm1qJiyqEZhMq2pItNK2', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01020647446', NULL, NULL, '1996-12-06', NULL, 'client', 1, NULL, '2024-12-25 10:52:48', '2024-12-25 10:53:06', NULL, NULL, NULL),
(60, 'khlood ibrahim', 'rasha@admin.com', NULL, '$2y$10$MfM05jWW0upP7JRDlzvwSecqGBVNvklf6pKLGos3Ht7k23mBT0Rn.', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01201001817', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2025-01-07 08:05:48', '2025-01-07 08:05:48', NULL, NULL, NULL),
(61, 'Touka Raafat', 'toka@gmail.com', NULL, '$2y$10$6gNs47H.DjH4LnoCUZtae.hxzu2qXySvFEv1AvhsGcEF3tLrUrPZm', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01097575849', NULL, NULL, '2025-01-22', NULL, 'client', 1, NULL, '2025-01-07 11:24:24', '2025-01-07 11:24:24', NULL, NULL, NULL),
(65, 'hend', 'hend@yahoo.com', NULL, '$2y$10$YtGAoXJ1Dr1kFn2PLN5V9OkIVHgZhA/D7vKXwPGk65wALHft8ERay', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01062501779', NULL, NULL, '2025-01-09', NULL, 'client', 1, NULL, '2025-01-09 11:34:12', '2025-01-09 11:34:12', NULL, NULL, NULL),
(67, 'احمد محمد', 'aaa@mail.com', NULL, '$2y$10$YEsJ.z5l0za4FxfYIDqQ5emCm3NOyf49QunBVew9tVfE43ByvApb.', NULL, NULL, '0122', NULL, NULL, NULL, NULL, 'admin', 1, NULL, '2025-01-13 10:40:31', '2025-01-13 10:40:31', NULL, NULL, NULL),
(70, 'ahmed', 'ahmed@email.com', NULL, '$2y$10$AQt4ncP.NMPnVgNvvQ2TlOpHN1vUXdHU1.hcrIJAPymoxfBHA580q', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01224567863', NULL, NULL, '2025-01-01', NULL, 'client', 1, NULL, '2025-01-21 12:37:04', '2025-01-29 08:45:52', NULL, NULL, '2025-01-29 08:45:52'),
(71, 'محمد حسن', 'mohamed@email.com', NULL, '$2y$10$Y45dQUErat9GETr3f/GjHuWlto/gPW2K3k1wJ.9n1Syd9mbr4L1Dq', NULL, NULL, '01224303330', NULL, NULL, NULL, NULL, 'admin', 1, NULL, '2025-01-22 15:04:23', '2025-01-22 15:04:23', NULL, NULL, NULL),
(72, 'kitchen manager', 'kitchenmanager@admin.com', NULL, '$2y$10$ocJ0bZ4kgYF.mkF1yR3T/egKS2qBWf6GB4pT3Jb0j/kk2mIubZ4H.', NULL, NULL, '01245785647', NULL, NULL, NULL, NULL, 'admin', 1, NULL, '2025-01-23 13:15:50', '2025-01-23 13:15:50', NULL, NULL, NULL),
(74, 'kitchen manager1', 'kitchenmanager1@admin.com', NULL, '$2y$10$oL.VCQ8V88.w60ih.GVD6.U7VedDPzC4sCBWcS6Vk/IN.ANUyZTtG', NULL, NULL, '01000416747', NULL, NULL, NULL, NULL, 'admin', 1, NULL, '2025-01-23 13:21:37', '2025-01-23 13:21:37', NULL, NULL, NULL),
(82, 'احمد محمد', 'ahmeddd@gmail.com', NULL, '$2y$10$QBdqWOLMLXxTOSiEK2TePOWzf30VzGW7qK41eUYa4/YRsv5hca5UO', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01111111111', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2025-01-27 10:57:54', '2025-01-29 08:45:45', NULL, NULL, '2025-01-29 08:45:45'),
(87, 'احمد محمد', 'ahmedd@gmail.com', NULL, '$2y$10$Id0MgRx3.leQuJRYmbPKi.iF8UM8aJbq4biuElYxISP04Kbz.v30W', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01224304440', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2025-01-27 14:11:47', '2025-01-29 08:45:29', NULL, NULL, '2025-01-29 08:45:29'),
(88, 'احمد عيد صديق حفنى', 'ahmed@gmail.com', NULL, '$2y$10$19.orygSXt3JiJ9F0mdmou5cupNecZl.yUJT/K2dfOmVYgUULuJB6', '9e80453d-561f-4f8c-8f99-865fd4ef8ecd', '+20', '01091510571', NULL, NULL, NULL, NULL, 'client', 1, NULL, '2025-01-29 12:18:24', '2025-01-29 12:18:24', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_coupons`
--

CREATE TABLE `user_coupons` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `coupon_id` bigint UNSIGNED NOT NULL,
  `status` enum('active','inactive','used') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_coupons`
--

INSERT INTO `user_coupons` (`id`, `user_id`, `coupon_id`, `status`, `created_by`, `modified_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 33, 12, 'active', 9, NULL, NULL, NULL, NULL, NULL),
(2, 33, 14, 'used', 9, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_favorite_dishes`
--

CREATE TABLE `user_favorite_dishes` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `dish_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_favorite_dishes`
--

INSERT INTO `user_favorite_dishes` (`id`, `user_id`, `dish_id`, `created_at`, `updated_at`) VALUES
(102, 33, 52, '2025-01-21 13:08:59', '2025-01-21 13:08:59'),
(104, 33, 129, '2025-01-22 07:29:07', '2025-01-22 07:29:07'),
(107, 33, 130, '2025-01-22 11:48:22', '2025-01-22 11:48:22'),
(108, 33, 144, '2025-01-22 13:17:40', '2025-01-22 13:17:40'),
(111, 33, 131, '2025-01-22 13:17:55', '2025-01-22 13:17:55');

-- --------------------------------------------------------

--
-- Table structure for table `user_gifts`
--

CREATE TABLE `user_gifts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `gift_id` bigint UNSIGNED NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1(used), 0(not used)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_gifts`
--

INSERT INTO `user_gifts` (`id`, `user_id`, `gift_id`, `used`, `created_at`, `updated_at`) VALUES
(1, 65, 1, 0, '2025-01-09 11:34:13', '2025-01-09 11:34:13'),
(2, 66, 1, 0, '2025-01-13 08:49:55', '2025-01-13 08:49:55'),
(3, 68, 1, 0, '2025-01-15 14:31:59', '2025-01-15 14:31:59'),
(4, 69, 1, 0, '2025-01-20 14:17:13', '2025-01-20 14:17:13'),
(5, 70, 1, 0, '2025-01-21 12:37:04', '2025-01-21 12:37:04'),
(6, 75, 1, 0, '2025-01-23 14:24:15', '2025-01-23 14:24:15'),
(7, 76, 1, 0, '2025-01-23 15:02:09', '2025-01-23 15:02:09'),
(8, 77, 1, 0, '2025-01-26 12:19:25', '2025-01-26 12:19:25'),
(9, 78, 1, 0, '2025-01-26 14:02:58', '2025-01-26 14:02:58'),
(10, 79, 1, 0, '2025-01-26 15:05:53', '2025-01-26 15:05:53'),
(11, 80, 1, 0, '2025-01-27 10:33:40', '2025-01-27 10:33:40'),
(12, 81, 1, 0, '2025-01-27 10:40:01', '2025-01-27 10:40:01'),
(13, 82, 1, 0, '2025-01-27 10:57:54', '2025-01-27 10:57:54'),
(14, 83, 1, 0, '2025-01-27 11:43:29', '2025-01-27 11:43:29'),
(15, 84, 1, 0, '2025-01-27 14:04:35', '2025-01-27 14:04:35'),
(16, 85, 1, 0, '2025-01-27 14:08:03', '2025-01-27 14:08:03'),
(17, 86, 1, 0, '2025-01-27 14:09:04', '2025-01-27 14:09:04'),
(18, 87, 1, 0, '2025-01-27 14:11:47', '2025-01-27 14:11:47'),
(19, 88, 1, 0, '2025-01-29 12:18:24', '2025-01-29 12:18:24');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` bigint UNSIGNED NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `address_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `country_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `actionbacklogs`
--
ALTER TABLE `actionbacklogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actionbacklogs_created_by_foreign` (`created_by`);

--
-- Indexes for table `activity_codes`
--
ALTER TABLE `activity_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `addon_categories`
--
ALTER TABLE `addon_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `advances`
--
ALTER TABLE `advances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advances_request_id_foreign` (`request_id`),
  ADD KEY `advances_employee_id_foreign` (`employee_id`),
  ADD KEY `advances_created_by_foreign` (`created_by`),
  ADD KEY `advances_modified_by_foreign` (`modified_by`),
  ADD KEY `advances_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `advance_requests`
--
ALTER TABLE `advance_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advance_requests_advance_setting_id_foreign` (`advance_setting_id`),
  ADD KEY `advance_requests_employee_id_foreign` (`employee_id`),
  ADD KEY `advance_requests_created_by_foreign` (`created_by`),
  ADD KEY `advance_requests_modified_by_foreign` (`modified_by`),
  ADD KEY `advance_requests_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `advance_settings`
--
ALTER TABLE `advance_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advance_settings_created_by_foreign` (`created_by`),
  ADD KEY `advance_settings_modified_by_foreign` (`modified_by`),
  ADD KEY `advance_settings_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `apicodes`
--
ALTER TABLE `apicodes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branches_country_id_foreign` (`country_id`),
  ADD KEY `branches_created_by_foreign` (`created_by`),
  ADD KEY `branches_modified_by_foreign` (`modified_by`),
  ADD KEY `branches_deleted_by_foreign` (`deleted_by`),
  ADD KEY `branches_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `branch_coupon`
--
ALTER TABLE `branch_coupon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_coupon_coupon_id_foreign` (`coupon_id`),
  ADD KEY `branch_coupon_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `branch_discount`
--
ALTER TABLE `branch_discount`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_discount_branch_id_foreign` (`branch_id`),
  ADD KEY `branch_discount_discount_id_foreign` (`discount_id`);

--
-- Indexes for table `branch_menus`
--
ALTER TABLE `branch_menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_menus_branch_id_foreign` (`branch_id`),
  ADD KEY `branch_menus_dish_id_foreign` (`dish_id`),
  ADD KEY `branch_menus_branch_menu_category_id_foreign` (`branch_menu_category_id`),
  ADD KEY `branch_menus_created_by_foreign` (`created_by`),
  ADD KEY `branch_menus_modified_by_foreign` (`modified_by`),
  ADD KEY `branch_menus_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `branch_menu_addons`
--
ALTER TABLE `branch_menu_addons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_menu_addons_branch_id_foreign` (`branch_id`),
  ADD KEY `branch_menu_addons_dish_addon_id_foreign` (`dish_addon_id`),
  ADD KEY `branch_menu_addons_created_by_foreign` (`created_by`),
  ADD KEY `branch_menu_addons_modified_by_foreign` (`modified_by`),
  ADD KEY `branch_menu_addons_deleted_by_foreign` (`deleted_by`),
  ADD KEY `branch_menu_addons_dish_id_foreign` (`dish_id`),
  ADD KEY `branch_menu_addons_branch_menu_addon_category_id_foreign` (`branch_menu_addon_category_id`);

--
-- Indexes for table `branch_menu_addon_categories`
--
ALTER TABLE `branch_menu_addon_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_menu_addon_categories_branch_id_foreign` (`branch_id`),
  ADD KEY `branch_menu_addon_categories_addon_category_id_foreign` (`addon_category_id`),
  ADD KEY `branch_menu_addon_categories_created_by_foreign` (`created_by`),
  ADD KEY `branch_menu_addon_categories_modified_by_foreign` (`modified_by`),
  ADD KEY `branch_menu_addon_categories_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `branch_menu_categories`
--
ALTER TABLE `branch_menu_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_menu_categories_dish_category_id_foreign` (`dish_category_id`),
  ADD KEY `branch_menu_categories_branch_id_foreign` (`branch_id`),
  ADD KEY `branch_menu_categories_created_by_foreign` (`created_by`),
  ADD KEY `branch_menu_categories_modified_by_foreign` (`modified_by`),
  ADD KEY `branch_menu_categories_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `branch_menu_sizes`
--
ALTER TABLE `branch_menu_sizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_menu_sizes_branch_id_foreign` (`branch_id`),
  ADD KEY `branch_menu_sizes_dish_size_id_foreign` (`dish_size_id`),
  ADD KEY `branch_menu_sizes_created_by_foreign` (`created_by`),
  ADD KEY `branch_menu_sizes_modified_by_foreign` (`modified_by`),
  ADD KEY `branch_menu_sizes_deleted_by_foreign` (`deleted_by`),
  ADD KEY `branch_menu_sizes_dish_id_foreign` (`dish_id`);

--
-- Indexes for table `branch_poses`
--
ALTER TABLE `branch_poses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_poses_posserial_unique` (`posserial`),
  ADD KEY `branch_poses_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `branch_recipe`
--
ALTER TABLE `branch_recipe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_recipe_branch_id_foreign` (`branch_id`),
  ADD KEY `branch_recipe_recipe_id_foreign` (`recipe_id`);

--
-- Indexes for table `branch_times`
--
ALTER TABLE `branch_times`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_times_branch_id_foreign` (`branch_id`),
  ADD KEY `branch_times_created_by_foreign` (`created_by`),
  ADD KEY `branch_times_modified_by_foreign` (`modified_by`),
  ADD KEY `branch_times_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brands_created_by_foreign` (`created_by`),
  ADD KEY `brands_modified_by_foreign` (`modified_by`),
  ADD KEY `brands_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `cashier_machines`
--
ALTER TABLE `cashier_machines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cashier_machines_branch_id_foreign` (`branch_id`),
  ADD KEY `cashier_machines_created_by_foreign` (`created_by`),
  ADD KEY `cashier_machines_modified_by_foreign` (`modified_by`),
  ADD KEY `cashier_machines_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `cashier_machine_logs`
--
ALTER TABLE `cashier_machine_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cashier_machine_logs_employee_id_foreign` (`employee_id`),
  ADD KEY `cashier_machine_logs_cashier_machine_id_foreign` (`cashier_machine_id`),
  ADD KEY `cashier_machine_logs_employee_opening_balance_id_foreign` (`employee_opening_balance_id`),
  ADD KEY `cashier_machine_logs_created_by_foreign` (`created_by`),
  ADD KEY `cashier_machine_logs_modified_by_foreign` (`modified_by`),
  ADD KEY `cashier_machine_logs_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_code_unique` (`code`),
  ADD KEY `categories_parent_id_foreign` (`parent_id`),
  ADD KEY `categories_created_by_foreign` (`created_by`),
  ADD KEY `categories_modify_by_foreign` (`modify_by`),
  ADD KEY `categories_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `client_addresses`
--
ALTER TABLE `client_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_addresses_user_id_foreign` (`user_id`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `colors_modified_by_foreign` (`modified_by`),
  ADD KEY `colors_created_by_foreign` (`created_by`),
  ADD KEY `colors_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `countries_modified_by_foreign` (`modified_by`),
  ADD KEY `countries_created_by_foreign` (`created_by`),
  ADD KEY `countries_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupons_created_by_foreign` (`created_by`),
  ADD KEY `coupons_modified_by_foreign` (`modified_by`),
  ADD KEY `coupons_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `cuisines`
--
ALTER TABLE `cuisines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cuisines_created_by_foreign` (`created_by`),
  ADD KEY `cuisines_modified_by_foreign` (`modified_by`),
  ADD KEY `cuisines_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `delays`
--
ALTER TABLE `delays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delays_time_id_foreign` (`time_id`),
  ADD KEY `delays_employee_id_foreign` (`employee_id`),
  ADD KEY `delays_created_by_foreign` (`created_by`),
  ADD KEY `delays_modified_by_foreign` (`modified_by`),
  ADD KEY `delays_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `delay_deductions`
--
ALTER TABLE `delay_deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delay_deductions_delay_id_foreign` (`delay_id`),
  ADD KEY `delay_deductions_employee_id_foreign` (`employee_id`),
  ADD KEY `delay_deductions_created_by_foreign` (`created_by`),
  ADD KEY `delay_deductions_modified_by_foreign` (`modified_by`),
  ADD KEY `delay_deductions_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `delay_times`
--
ALTER TABLE `delay_times`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delay_times_created_by_foreign` (`created_by`),
  ADD KEY `delay_times_modified_by_foreign` (`modified_by`),
  ADD KEY `delay_times_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `delivery_settings`
--
ALTER TABLE `delivery_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_settings_branch_id_foreign` (`branch_id`),
  ADD KEY `delivery_settings_created_by_foreign` (`created_by`),
  ADD KEY `delivery_settings_modified_by_foreign` (`modified_by`),
  ADD KEY `delivery_settings_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_name_en_unique` (`name_en`),
  ADD UNIQUE KEY `departments_name_ar_unique` (`name_ar`),
  ADD KEY `departments_parent_id_foreign` (`parent_id`),
  ADD KEY `departments_created_by_foreign` (`created_by`),
  ADD KEY `departments_modified_by_foreign` (`modified_by`),
  ADD KEY `departments_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `discounts_created_by_foreign` (`created_by`),
  ADD KEY `discounts_modified_by_foreign` (`modified_by`),
  ADD KEY `discounts_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dishes_code_unique` (`code`),
  ADD KEY `dishes_category_id_foreign` (`category_id`),
  ADD KEY `dishes_cuisine_id_foreign` (`cuisine_id`),
  ADD KEY `dishes_created_by_foreign` (`created_by`),
  ADD KEY `dishes_modified_by_foreign` (`modified_by`),
  ADD KEY `dishes_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `dish_addons`
--
ALTER TABLE `dish_addons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_addons_addon_id_foreign` (`addon_id`),
  ADD KEY `dish_addons_dish_id_foreign` (`dish_id`),
  ADD KEY `dish_addons_addon_category_id_foreign` (`addon_category_id`);

--
-- Indexes for table `dish_categories`
--
ALTER TABLE `dish_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dish_categories_parent_id_foreign` (`parent_id`),
  ADD KEY `dish_categories_created_by_foreign` (`created_by`),
  ADD KEY `dish_categories_modified_by_foreign` (`modified_by`),
  ADD KEY `dish_categories_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `dish_details`
--
ALTER TABLE `dish_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dish_details_dish_id_foreign` (`dish_id`),
  ADD KEY `dish_details_recipe_id_foreign` (`recipe_id`),
  ADD KEY `dish_details_dish_size_id_foreign` (`dish_size_id`);

--
-- Indexes for table `dish_discount`
--
ALTER TABLE `dish_discount`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dish_discount_dish_id_foreign` (`dish_id`),
  ADD KEY `dish_discount_discount_id_foreign` (`discount_id`),
  ADD KEY `dish_discount_created_by_foreign` (`created_by`),
  ADD KEY `dish_discount_modify_by_foreign` (`modify_by`),
  ADD KEY `dish_discount_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `dish_sizes`
--
ALTER TABLE `dish_sizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dish_sizes_dish_id_foreign` (`dish_id`);

--
-- Indexes for table `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `divisions_created_by_foreign` (`created_by`),
  ADD KEY `divisions_modified_by_foreign` (`modified_by`),
  ADD KEY `divisions_deleted_by_foreign` (`deleted_by`),
  ADD KEY `divisions_line_id_foreign` (`line_id`);

--
-- Indexes for table `domains`
--
ALTER TABLE `domains`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domains_domain_unique` (`domain`),
  ADD KEY `domains_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `einvoices`
--
ALTER TABLE `einvoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `einvoices_invoice_id_unique` (`invoice_id`),
  ADD KEY `einvoices_reference_invoice_id_foreign` (`reference_invoice_id`);

--
-- Indexes for table `einvoice_settings`
--
ALTER TABLE `einvoice_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `einvoice_settings_key_unique` (`key`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_employee_code_unique` (`employee_code`),
  ADD UNIQUE KEY `employees_email_unique` (`email`),
  ADD UNIQUE KEY `employees_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `employees_national_id_unique` (`national_id`),
  ADD KEY `employees_created_by_foreign` (`created_by`),
  ADD KEY `employees_modified_by_foreign` (`modified_by`),
  ADD KEY `employees_deleted_by_foreign` (`deleted_by`),
  ADD KEY `employees_nationality_id_foreign` (`nationality_id`),
  ADD KEY `employees_branch_id_foreign` (`branch_id`),
  ADD KEY `employees_supervisor_id_foreign` (`supervisor_id`);

--
-- Indexes for table `employee_floor_partitions`
--
ALTER TABLE `employee_floor_partitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_floor_partitions_floor_partition_id_foreign` (`floor_partition_id`),
  ADD KEY `employee_floor_partitions_employee_id_foreign` (`employee_id`),
  ADD KEY `employee_floor_partitions_created_by_foreign` (`created_by`),
  ADD KEY `employee_floor_partitions_modified_by_foreign` (`modified_by`),
  ADD KEY `employee_floor_partitions_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `employee_opening_balances`
--
ALTER TABLE `employee_opening_balances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_opening_balances_employee_id_foreign` (`employee_id`),
  ADD KEY `employee_opening_balances_cashier_machine_id_foreign` (`cashier_machine_id`),
  ADD KEY `employee_opening_balances_employee_schedule_id_foreign` (`employee_schedule_id`),
  ADD KEY `employee_opening_balances_created_by_foreign` (`created_by`),
  ADD KEY `employee_opening_balances_modified_by_foreign` (`modified_by`),
  ADD KEY `employee_opening_balances_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `employee_schedules`
--
ALTER TABLE `employee_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_schedules_employee_id_foreign` (`employee_id`),
  ADD KEY `employee_schedules_shift_id_foreign` (`shift_id`),
  ADD KEY `employee_schedules_created_by_foreign` (`created_by`),
  ADD KEY `employee_schedules_modified_by_foreign` (`modified_by`),
  ADD KEY `employee_schedules_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `excuses`
--
ALTER TABLE `excuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `excuses_employee_id_foreign` (`employee_id`),
  ADD KEY `excuses_excuse_request_id_foreign` (`excuse_request_id`),
  ADD KEY `excuses_approver_id_foreign` (`approver_id`),
  ADD KEY `excuses_created_by_foreign` (`created_by`),
  ADD KEY `excuses_modified_by_foreign` (`modified_by`);

--
-- Indexes for table `excuse_requests`
--
ALTER TABLE `excuse_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `excuse_requests_employee_id_foreign` (`employee_id`),
  ADD KEY `excuse_requests_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `excuse_settings`
--
ALTER TABLE `excuse_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `floors`
--
ALTER TABLE `floors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `floors_modified_by_foreign` (`modified_by`),
  ADD KEY `floors_deleted_by_foreign` (`deleted_by`),
  ADD KEY `floors_branch_id_foreign` (`branch_id`),
  ADD KEY `floors_created_by_foreign` (`created_by`);

--
-- Indexes for table `floor_partitions`
--
ALTER TABLE `floor_partitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `floor_partitions_floor_id_foreign` (`floor_id`),
  ADD KEY `floor_partitions_modified_by_foreign` (`modified_by`),
  ADD KEY `floor_partitions_deleted_by_foreign` (`deleted_by`),
  ADD KEY `floor_partitions_created_by_foreign` (`created_by`);

--
-- Indexes for table `f_a_q_s`
--
ALTER TABLE `f_a_q_s`
  ADD PRIMARY KEY (`id`),
  ADD KEY `f_a_q_s_created_by_foreign` (`created_by`),
  ADD KEY `f_a_q_s_modified_by_foreign` (`modified_by`),
  ADD KEY `f_a_q_s_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `gifts`
--
ALTER TABLE `gifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ingredients_recipe_id_foreign` (`recipe_id`),
  ADD KEY `ingredients_product_id_foreign` (`product_id`),
  ADD KEY `ingredients_product_unit_id_foreign` (`product_unit_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `kitchen_logs`
--
ALTER TABLE `kitchen_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kitchen_logs_order_id_foreign` (`order_id`),
  ADD KEY `kitchen_logs_order_details_id_foreign` (`order_details_id`),
  ADD KEY `kitchen_logs_dish_id_foreign` (`dish_id`),
  ADD KEY `kitchen_logs_dish_size_id_foreign` (`dish_size_id`),
  ADD KEY `kitchen_logs_offer_id_foreign` (`offer_id`),
  ADD KEY `kitchen_logs_branch_id_foreign` (`branch_id`),
  ADD KEY `kitchen_logs_store_id_foreign` (`store_id`),
  ADD KEY `kitchen_logs_order_addone_id_foreign` (`order_addone_id`),
  ADD KEY `kitchen_logs_dish_addone_id_foreign` (`dish_addone_id`),
  ADD KEY `kitchen_logs_table_id_foreign` (`table_id`),
  ADD KEY `kitchen_logs_employee_id_foreign` (`employee_id`),
  ADD KEY `kitchen_logs_created_by_foreign` (`created_by`),
  ADD KEY `kitchen_logs_modified_by_foreign` (`modified_by`),
  ADD KEY `kitchen_logs_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `leave_nationals`
--
ALTER TABLE `leave_nationals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_nationals_country_id_foreign` (`country_id`),
  ADD KEY `leave_nationals_leave_type_id_foreign` (`leave_type_id`),
  ADD KEY `leave_nationals_created_by_foreign` (`created_by`),
  ADD KEY `leave_nationals_modified_by_foreign` (`modified_by`),
  ADD KEY `leave_nationals_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_requests_employee_id_foreign` (`employee_id`),
  ADD KEY `leave_requests_leave_type_id_foreign` (`leave_type_id`),
  ADD KEY `leave_requests_agreement_by_foreign` (`agreement_by`),
  ADD KEY `leave_requests_created_by_foreign` (`created_by`),
  ADD KEY `leave_requests_modified_by_foreign` (`modified_by`),
  ADD KEY `leave_requests_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `leave_settings`
--
ALTER TABLE `leave_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_settings_country_id_foreign` (`country_id`),
  ADD KEY `leave_settings_leave_type_id_foreign` (`leave_type_id`),
  ADD KEY `leave_settings_created_by_foreign` (`created_by`),
  ADD KEY `leave_settings_modified_by_foreign` (`modified_by`),
  ADD KEY `leave_settings_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_types_created_by_foreign` (`created_by`),
  ADD KEY `leave_types_modified_by_foreign` (`modified_by`),
  ADD KEY `leave_types_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `lines`
--
ALTER TABLE `lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lines_created_by_foreign` (`created_by`),
  ADD KEY `lines_modified_by_foreign` (`modified_by`),
  ADD KEY `lines_deleted_by_foreign` (`deleted_by`),
  ADD KEY `lines_store_id_foreign` (`store_id`);

--
-- Indexes for table `logos`
--
ALTER TABLE `logos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logos_created_by_foreign` (`created_by`),
  ADD KEY `logos_modified_by_foreign` (`modified_by`),
  ADD KEY `logos_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `nationalities`
--
ALTER TABLE `nationalities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nationalities_created_by_foreign` (`created_by`),
  ADD KEY `nationalities_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_foreign` (`user_id`),
  ADD KEY `notifications_modified_by_foreign` (`modified_by`),
  ADD KEY `notifications_created_by_foreign` (`created_by`),
  ADD KEY `notifications_product_id_foreign` (`product_id`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_auth_codes_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `offers_created_by_foreign` (`created_by`),
  ADD KEY `offers_modified_by_foreign` (`modified_by`),
  ADD KEY `offers_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `offer_details`
--
ALTER TABLE `offer_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `offer_details_offer_id_foreign` (`offer_id`),
  ADD KEY `offer_details_created_by_foreign` (`created_by`),
  ADD KEY `offer_details_modified_by_foreign` (`modified_by`),
  ADD KEY `offer_details_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `opening_balance`
--
ALTER TABLE `opening_balance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `opening_balance_created_by_foreign` (`created_by`),
  ADD KEY `opening_balance_deleted_by_foreign` (`deleted_by`),
  ADD KEY `opening_balance_product_id_foreign` (`product_id`),
  ADD KEY `opening_balance_store_id_foreign` (`store_id`),
  ADD KEY `opening_balance_modified_by_foreign` (`modified_by`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_client_id_foreign` (`client_id`),
  ADD KEY `orders_table_id_foreign` (`table_id`),
  ADD KEY `orders_created_by_foreign` (`created_by`),
  ADD KEY `orders_modify_by_foreign` (`modify_by`),
  ADD KEY `orders_deleted_by_foreign` (`deleted_by`),
  ADD KEY `orders_discount_id_foreign` (`discount_id`),
  ADD KEY `orders_coupon_id_foreign` (`coupon_id`),
  ADD KEY `orders_branch_id_foreign` (`branch_id`),
  ADD KEY `orders_client_address_id_foreign` (`client_address_id`);

--
-- Indexes for table `order_addons`
--
ALTER TABLE `order_addons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_addons_order_id_foreign` (`order_id`),
  ADD KEY `order_addons_created_by_foreign` (`created_by`),
  ADD KEY `order_addons_modify_by_foreign` (`modify_by`),
  ADD KEY `order_addons_deleted_by_foreign` (`deleted_by`),
  ADD KEY `order_addons_dish_addon_id_foreign` (`dish_addon_id`),
  ADD KEY `order_addons_order_details_id_foreign` (`order_details_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_details_order_id_foreign` (`order_id`),
  ADD KEY `order_details_created_by_foreign` (`created_by`),
  ADD KEY `order_details_modify_by_foreign` (`modify_by`),
  ADD KEY `order_details_deleted_by_foreign` (`deleted_by`),
  ADD KEY `order_details_dish_id_foreign` (`dish_id`),
  ADD KEY `order_details_offer_id_foreign` (`offer_id`),
  ADD KEY `order_details_dish_size_id_foreign` (`dish_size_id`);

--
-- Indexes for table `order_products`
--
ALTER TABLE `order_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_products_order_id_foreign` (`order_id`),
  ADD KEY `order_products_product_id_foreign` (`product_id`),
  ADD KEY `order_products_created_by_foreign` (`created_by`),
  ADD KEY `order_products_modified_by_foreign` (`modified_by`),
  ADD KEY `order_products_deleted_by_foreign` (`deleted_by`),
  ADD KEY `order_products_product_unit_id_foreign` (`product_unit_id`);

--
-- Indexes for table `order_refunds`
--
ALTER TABLE `order_refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_refunds_created_by_foreign` (`created_by`),
  ADD KEY `order_refunds_modify_by_foreign` (`modify_by`),
  ADD KEY `order_refunds_deleted_by_foreign` (`deleted_by`),
  ADD KEY `order_refunds_order_id_foreign` (`order_id`);

--
-- Indexes for table `order_trackings`
--
ALTER TABLE `order_trackings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_trackings_order_id_foreign` (`order_id`),
  ADD KEY `order_trackings_created_by_foreign` (`created_by`),
  ADD KEY `order_trackings_modify_by_foreign` (`modify_by`),
  ADD KEY `order_trackings_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `order_transactions`
--
ALTER TABLE `order_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_transactions_order_id_foreign` (`order_id`),
  ADD KEY `order_transactions_created_by_foreign` (`created_by`),
  ADD KEY `order_transactions_modify_by_foreign` (`modify_by`),
  ADD KEY `order_transactions_deleted_by_foreign` (`deleted_by`),
  ADD KEY `order_transactions_discount_id_foreign` (`discount_id`),
  ADD KEY `order_transactions_coupon_id_foreign` (`coupon_id`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `overtime_settings`
--
ALTER TABLE `overtime_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `overtime_settings_overtime_type_id_foreign` (`overtime_type_id`),
  ADD KEY `overtime_settings_created_by_foreign` (`created_by`),
  ADD KEY `overtime_settings_modified_by_foreign` (`modified_by`),
  ADD KEY `overtime_settings_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `overtime_types`
--
ALTER TABLE `overtime_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `overtime_types_created_by_foreign` (`created_by`),
  ADD KEY `overtime_types_modified_by_foreign` (`modified_by`),
  ADD KEY `overtime_types_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_methods_code_unique` (`code`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payrolls_employee_id_foreign` (`employee_id`),
  ADD KEY `payrolls_created_by_foreign` (`created_by`),
  ADD KEY `payrolls_modified_by_foreign` (`modified_by`),
  ADD KEY `payrolls_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `penalties`
--
ALTER TABLE `penalties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penalties_reason_id_foreign` (`reason_id`),
  ADD KEY `penalties_employee_id_foreign` (`employee_id`),
  ADD KEY `penalties_created_by_foreign` (`created_by`),
  ADD KEY `penalties_modified_by_foreign` (`modified_by`),
  ADD KEY `penalties_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `penalty_deductions`
--
ALTER TABLE `penalty_deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penalty_deductions_penalty_id_foreign` (`penalty_id`),
  ADD KEY `penalty_deductions_employee_id_foreign` (`employee_id`),
  ADD KEY `penalty_deductions_created_by_foreign` (`created_by`),
  ADD KEY `penalty_deductions_modified_by_foreign` (`modified_by`),
  ADD KEY `penalty_deductions_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `penalty_reasons`
--
ALTER TABLE `penalty_reasons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penalty_reasons_created_by_foreign` (`created_by`),
  ADD KEY `penalty_reasons_modified_by_foreign` (`modified_by`),
  ADD KEY `penalty_reasons_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `point_products`
--
ALTER TABLE `point_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `point_products_dish_id_foreign` (`dish_id`),
  ADD KEY `point_products_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `point_systems`
--
ALTER TABLE `point_systems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `point_systems_created_by_foreign` (`created_by`),
  ADD KEY `point_systems_modified_by_foreign` (`modified_by`),
  ADD KEY `point_systems_deleted_by_foreign` (`deleted_by`),
  ADD KEY `point_systems_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `point_transactions`
--
ALTER TABLE `point_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `point_transactions_customer_id_foreign` (`customer_id`),
  ADD KEY `point_transactions_order_id_foreign` (`order_id`),
  ADD KEY `point_transactions_created_by_foreign` (`created_by`),
  ADD KEY `point_transactions_modified_by_foreign` (`modified_by`),
  ADD KEY `point_transactions_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `positions_department_id_foreign` (`department_id`),
  ADD KEY `positions_created_by_foreign` (`created_by`),
  ADD KEY `positions_modified_by_foreign` (`modified_by`),
  ADD KEY `positions_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `privacy_policies`
--
ALTER TABLE `privacy_policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `privacy_policies_created_by_foreign` (`created_by`),
  ADD KEY `privacy_policies_modified_by_foreign` (`modified_by`),
  ADD KEY `privacy_policies_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_code_unique` (`code`),
  ADD KEY `products_created_by_foreign` (`created_by`),
  ADD KEY `products_modify_by_foreign` (`modify_by`),
  ADD KEY `products_deleted_by_foreign` (`deleted_by`),
  ADD KEY `products_main_unit_id_foreign` (`main_unit_id`),
  ADD KEY `products_currency_code_foreign` (`currency_code`),
  ADD KEY `products_category_id_foreign` (`category_id`),
  ADD KEY `products_brand_id_foreign` (`brand_id`);

--
-- Indexes for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_colors_created_by_foreign` (`created_by`),
  ADD KEY `product_colors_modify_by_foreign` (`modify_by`),
  ADD KEY `product_colors_deleted_by_foreign` (`deleted_by`),
  ADD KEY `product_colors_color_id_foreign` (`color_id`),
  ADD KEY `product_colors_product_id_foreign` (`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_foreign` (`product_id`),
  ADD KEY `product_images_created_by_foreign` (`created_by`),
  ADD KEY `product_images_modify_by_foreign` (`modify_by`),
  ADD KEY `product_images_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `product_limit`
--
ALTER TABLE `product_limit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_limit_product_id_foreign` (`product_id`),
  ADD KEY `product_limit_store_id_foreign` (`store_id`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_sizes_created_by_foreign` (`created_by`),
  ADD KEY `product_sizes_modify_by_foreign` (`modify_by`),
  ADD KEY `product_sizes_deleted_by_foreign` (`deleted_by`),
  ADD KEY `product_sizes_size_id_foreign` (`size_id`),
  ADD KEY `product_sizes_product_id_foreign` (`product_id`);

--
-- Indexes for table `product_transactions`
--
ALTER TABLE `product_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_transactions_product_id_foreign` (`product_id`),
  ADD KEY `product_transactions_store_id_foreign` (`store_id`),
  ADD KEY `product_transactions_product_size_id_foreign` (`product_size_id`),
  ADD KEY `product_transactions_product_color_id_foreign` (`product_color_id`),
  ADD KEY `product_transactions_modified_by_foreign` (`modified_by`),
  ADD KEY `product_transactions_deleted_by_foreign` (`deleted_by`),
  ADD KEY `product_transactions_created_by_foreign` (`created_by`);

--
-- Indexes for table `product_transaction_logs`
--
ALTER TABLE `product_transaction_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_transaction_logs_product_id_foreign` (`product_id`),
  ADD KEY `product_transaction_logs_store_id_foreign` (`store_id`),
  ADD KEY `product_transaction_logs_product_size_id_foreign` (`product_size_id`),
  ADD KEY `product_transaction_logs_product_color_id_foreign` (`product_color_id`),
  ADD KEY `product_transaction_logs_modified_by_foreign` (`modified_by`),
  ADD KEY `product_transaction_logs_deleted_by_foreign` (`deleted_by`),
  ADD KEY `product_transaction_logs_created_by_foreign` (`created_by`);

--
-- Indexes for table `product_units`
--
ALTER TABLE `product_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_units_created_by_foreign` (`created_by`),
  ADD KEY `product_units_deleted_by_foreign` (`deleted_by`),
  ADD KEY `product_units_modify_by_foreign` (`modify_by`),
  ADD KEY `product_units_unit_id_foreign` (`unit_id`),
  ADD KEY `product_units_product_id_foreign` (`product_id`);

--
-- Indexes for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `purchase_invoices_vendor_id_foreign` (`vendor_id`),
  ADD KEY `purchase_invoices_store_id_foreign` (`store_id`),
  ADD KEY `purchase_invoices_created_by_foreign` (`created_by`),
  ADD KEY `purchase_invoices_modified_by_foreign` (`modified_by`),
  ADD KEY `purchase_invoices_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `purchase_invoices_details`
--
ALTER TABLE `purchase_invoices_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_invoices_details_purchase_invoices_id_foreign` (`purchase_invoices_id`),
  ADD KEY `purchase_invoices_details_category_id_foreign` (`category_id`),
  ADD KEY `purchase_invoices_details_product_id_foreign` (`product_id`),
  ADD KEY `purchase_invoices_details_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `rates`
--
ALTER TABLE `rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rates_created_by_foreign` (`created_by`),
  ADD KEY `rates_modified_by_foreign` (`modified_by`),
  ADD KEY `rates_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `recipes_code_unique` (`code`),
  ADD KEY `recipes_created_by_foreign` (`created_by`),
  ADD KEY `recipes_modified_by_foreign` (`modified_by`),
  ADD KEY `recipes_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `recipe_images`
--
ALTER TABLE `recipe_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_images_recipe_id_foreign` (`recipe_id`);

--
-- Indexes for table `return_policies`
--
ALTER TABLE `return_policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `return_policies_created_by_foreign` (`created_by`),
  ADD KEY `return_policies_modified_by_foreign` (`modified_by`),
  ADD KEY `return_policies_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shelves`
--
ALTER TABLE `shelves`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shelves_code_unique` (`code`),
  ADD KEY `shelves_division_id_foreign` (`division_id`),
  ADD KEY `shelves_created_by_foreign` (`created_by`),
  ADD KEY `shelves_modified_by_foreign` (`modified_by`),
  ADD KEY `shelves_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shifts_created_by_foreign` (`created_by`),
  ADD KEY `shifts_modified_by_foreign` (`modified_by`),
  ADD KEY `shifts_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `shift_details`
--
ALTER TABLE `shift_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shift_details_shift_id_foreign` (`shift_id`),
  ADD KEY `shift_details_timetable_id_foreign` (`timetable_id`),
  ADD KEY `shift_details_created_by_foreign` (`created_by`),
  ADD KEY `shift_details_modified_by_foreign` (`modified_by`),
  ADD KEY `shift_details_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `sizes`
--
ALTER TABLE `sizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sizes_category_id_foreign` (`category_id`),
  ADD KEY `sizes_modified_by_foreign` (`modified_by`),
  ADD KEY `sizes_created_by_foreign` (`created_by`),
  ADD KEY `sizes_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sliders_created_by_foreign` (`created_by`),
  ADD KEY `sliders_modified_by_foreign` (`modified_by`),
  ADD KEY `sliders_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stores_branch_id_foreign` (`branch_id`),
  ADD KEY `stores_created_by_foreign` (`created_by`),
  ADD KEY `stores_modified_by_foreign` (`modified_by`),
  ADD KEY `stores_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `store_categories`
--
ALTER TABLE `store_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_categories_store_id_foreign` (`store_id`),
  ADD KEY `store_categories_category_id_foreign` (`category_id`);

--
-- Indexes for table `store_transactions`
--
ALTER TABLE `store_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_transactions_user_id_foreign` (`user_id`),
  ADD KEY `store_transactions_store_id_foreign` (`store_id`),
  ADD KEY `store_transactions_modified_by_foreign` (`modified_by`),
  ADD KEY `store_transactions_deleted_by_foreign` (`deleted_by`),
  ADD KEY `store_transactions_branch_id_foreign` (`branch_id`),
  ADD KEY `store_transactions_created_by_foreign` (`created_by`);

--
-- Indexes for table `store_transaction_details`
--
ALTER TABLE `store_transaction_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_transaction_details_store_transaction_id_foreign` (`store_transaction_id`),
  ADD KEY `store_transaction_details_product_id_foreign` (`product_id`),
  ADD KEY `store_transaction_details_product_unit_id_foreign` (`product_unit_id`),
  ADD KEY `store_transaction_details_product_size_id_foreign` (`product_size_id`),
  ADD KEY `store_transaction_details_product_color_id_foreign` (`product_color_id`),
  ADD KEY `store_transaction_details_country_id_foreign` (`country_id`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tables_floor_id_foreign` (`floor_id`),
  ADD KEY `tables_modified_by_foreign` (`modified_by`),
  ADD KEY `tables_deleted_by_foreign` (`deleted_by`),
  ADD KEY `tables_floor_partition_id_foreign` (`floor_partition_id`),
  ADD KEY `tables_created_by_foreign` (`created_by`);

--
-- Indexes for table `table_reservations`
--
ALTER TABLE `table_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `table_reservations_table_id_foreign` (`table_id`),
  ADD KEY `table_reservations_client_id_foreign` (`client_id`),
  ADD KEY `table_reservations_confirmed_by_foreign` (`confirmed_by`),
  ADD KEY `table_reservations_modified_by_foreign` (`modified_by`),
  ADD KEY `table_reservations_deleted_by_foreign` (`deleted_by`),
  ADD KEY `table_reservations_created_by_foreign` (`created_by`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `terms_and_conditions`
--
ALTER TABLE `terms_and_conditions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `terms_and_conditions_created_by_foreign` (`created_by`),
  ADD KEY `terms_and_conditions_modified_by_foreign` (`modified_by`),
  ADD KEY `terms_and_conditions_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timetables_created_by_foreign` (`created_by`),
  ADD KEY `timetables_modified_by_foreign` (`modified_by`),
  ADD KEY `timetables_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `units_created_by_foreign` (`created_by`),
  ADD KEY `units_modify_by_foreign` (`modify_by`),
  ADD KEY `units_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `unit_types`
--
ALTER TABLE `unit_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unit_types_code_unique` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_country_id_foreign` (`country_id`);

--
-- Indexes for table `user_coupons`
--
ALTER TABLE `user_coupons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_coupons_user_id_foreign` (`user_id`),
  ADD KEY `user_coupons_coupon_id_foreign` (`coupon_id`),
  ADD KEY `user_coupons_created_by_foreign` (`created_by`),
  ADD KEY `user_coupons_modified_by_foreign` (`modified_by`),
  ADD KEY `user_coupons_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `user_favorite_dishes`
--
ALTER TABLE `user_favorite_dishes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_favorite_dishes_user_id_foreign` (`user_id`),
  ADD KEY `user_favorite_dishes_dish_id_foreign` (`dish_id`);

--
-- Indexes for table `user_gifts`
--
ALTER TABLE `user_gifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_gifts_user_id_foreign` (`user_id`),
  ADD KEY `user_gifts_gift_id_foreign` (`gift_id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendors_country_id_foreign` (`country_id`),
  ADD KEY `vendors_created_by_foreign` (`created_by`),
  ADD KEY `vendors_modified_by_foreign` (`modified_by`),
  ADD KEY `vendors_deleted_by_foreign` (`deleted_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `actionbacklogs`
--
ALTER TABLE `actionbacklogs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `addon_categories`
--
ALTER TABLE `addon_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `advances`
--
ALTER TABLE `advances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `advance_requests`
--
ALTER TABLE `advance_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `advance_settings`
--
ALTER TABLE `advance_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `apicodes`
--
ALTER TABLE `apicodes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `branch_coupon`
--
ALTER TABLE `branch_coupon`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `branch_discount`
--
ALTER TABLE `branch_discount`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `branch_menus`
--
ALTER TABLE `branch_menus`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200;

--
-- AUTO_INCREMENT for table `branch_menu_addons`
--
ALTER TABLE `branch_menu_addons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=322;

--
-- AUTO_INCREMENT for table `branch_menu_addon_categories`
--
ALTER TABLE `branch_menu_addon_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `branch_menu_categories`
--
ALTER TABLE `branch_menu_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `branch_menu_sizes`
--
ALTER TABLE `branch_menu_sizes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT for table `branch_poses`
--
ALTER TABLE `branch_poses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branch_recipe`
--
ALTER TABLE `branch_recipe`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branch_times`
--
ALTER TABLE `branch_times`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cashier_machines`
--
ALTER TABLE `cashier_machines`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cashier_machine_logs`
--
ALTER TABLE `cashier_machine_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `client_addresses`
--
ALTER TABLE `client_addresses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `cuisines`
--
ALTER TABLE `cuisines`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `delays`
--
ALTER TABLE `delays`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `delay_deductions`
--
ALTER TABLE `delay_deductions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `delay_times`
--
ALTER TABLE `delay_times`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `delivery_settings`
--
ALTER TABLE `delivery_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `dish_addons`
--
ALTER TABLE `dish_addons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `dish_categories`
--
ALTER TABLE `dish_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `dish_details`
--
ALTER TABLE `dish_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `dish_discount`
--
ALTER TABLE `dish_discount`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `dish_sizes`
--
ALTER TABLE `dish_sizes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `divisions`
--
ALTER TABLE `divisions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `domains`
--
ALTER TABLE `domains`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `einvoices`
--
ALTER TABLE `einvoices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `einvoice_settings`
--
ALTER TABLE `einvoice_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employee_floor_partitions`
--
ALTER TABLE `employee_floor_partitions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_opening_balances`
--
ALTER TABLE `employee_opening_balances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_schedules`
--
ALTER TABLE `employee_schedules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `excuses`
--
ALTER TABLE `excuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `excuse_requests`
--
ALTER TABLE `excuse_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `excuse_settings`
--
ALTER TABLE `excuse_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `floors`
--
ALTER TABLE `floors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `floor_partitions`
--
ALTER TABLE `floor_partitions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gifts`
--
ALTER TABLE `gifts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kitchen_logs`
--
ALTER TABLE `kitchen_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_nationals`
--
ALTER TABLE `leave_nationals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lines`
--
ALTER TABLE `lines`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=435;

--
-- AUTO_INCREMENT for table `nationalities`
--
ALTER TABLE `nationalities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `offer_details`
--
ALTER TABLE `offer_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `opening_balance`
--
ALTER TABLE `opening_balance`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `order_addons`
--
ALTER TABLE `order_addons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `order_products`
--
ALTER TABLE `order_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_refunds`
--
ALTER TABLE `order_refunds`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_trackings`
--
ALTER TABLE `order_trackings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `order_transactions`
--
ALTER TABLE `order_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `overtime_settings`
--
ALTER TABLE `overtime_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `overtime_types`
--
ALTER TABLE `overtime_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `penalties`
--
ALTER TABLE `penalties`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `penalty_deductions`
--
ALTER TABLE `penalty_deductions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `penalty_reasons`
--
ALTER TABLE `penalty_reasons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=495;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `point_products`
--
ALTER TABLE `point_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `point_systems`
--
ALTER TABLE `point_systems`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `point_transactions`
--
ALTER TABLE `point_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `product_limit`
--
ALTER TABLE `product_limit`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_transactions`
--
ALTER TABLE `product_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_transaction_logs`
--
ALTER TABLE `product_transaction_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_units`
--
ALTER TABLE `product_units`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_invoices_details`
--
ALTER TABLE `purchase_invoices_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rates`
--
ALTER TABLE `rates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `recipe_images`
--
ALTER TABLE `recipe_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shelves`
--
ALTER TABLE `shelves`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shift_details`
--
ALTER TABLE `shift_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sizes`
--
ALTER TABLE `sizes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `store_categories`
--
ALTER TABLE `store_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `store_transactions`
--
ALTER TABLE `store_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `store_transaction_details`
--
ALTER TABLE `store_transaction_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `table_reservations`
--
ALTER TABLE `table_reservations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `unit_types`
--
ALTER TABLE `unit_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `user_coupons`
--
ALTER TABLE `user_coupons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_favorite_dishes`
--
ALTER TABLE `user_favorite_dishes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `user_gifts`
--
ALTER TABLE `user_gifts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `actionbacklogs`
--
ALTER TABLE `actionbacklogs`
  ADD CONSTRAINT `actionbacklogs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `advances`
--
ALTER TABLE `advances`
  ADD CONSTRAINT `advances_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `advances_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `advances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `advances_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `advances_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `advance_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `advance_requests`
--
ALTER TABLE `advance_requests`
  ADD CONSTRAINT `advance_requests_advance_setting_id_foreign` FOREIGN KEY (`advance_setting_id`) REFERENCES `advance_settings` (`id`),
  ADD CONSTRAINT `advance_requests_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `advance_requests_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `advance_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `advance_requests_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `advance_settings`
--
ALTER TABLE `advance_settings`
  ADD CONSTRAINT `advance_settings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `advance_settings_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `advance_settings_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `branches_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `branches_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `branches_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `branches_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `branch_coupon`
--
ALTER TABLE `branch_coupon`
  ADD CONSTRAINT `branch_coupon_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_coupon_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_discount`
--
ALTER TABLE `branch_discount`
  ADD CONSTRAINT `branch_discount_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_discount_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_menus`
--
ALTER TABLE `branch_menus`
  ADD CONSTRAINT `branch_menus_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menus_branch_menu_category_id_foreign` FOREIGN KEY (`branch_menu_category_id`) REFERENCES `branch_menu_categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menus_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menus_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menus_dish_id_foreign` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menus_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `branch_menu_addons`
--
ALTER TABLE `branch_menu_addons`
  ADD CONSTRAINT `branch_menu_addons_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_addons_branch_menu_addon_category_id_foreign` FOREIGN KEY (`branch_menu_addon_category_id`) REFERENCES `branch_menu_addon_categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_addons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_addons_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_addons_dish_addon_id_foreign` FOREIGN KEY (`dish_addon_id`) REFERENCES `dish_addons` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_addons_dish_id_foreign` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_addons_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `branch_menu_addon_categories`
--
ALTER TABLE `branch_menu_addon_categories`
  ADD CONSTRAINT `branch_menu_addon_categories_addon_category_id_foreign` FOREIGN KEY (`addon_category_id`) REFERENCES `addon_categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_addon_categories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_addon_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_addon_categories_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_addon_categories_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `branch_menu_categories`
--
ALTER TABLE `branch_menu_categories`
  ADD CONSTRAINT `branch_menu_categories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_categories_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_categories_dish_category_id_foreign` FOREIGN KEY (`dish_category_id`) REFERENCES `dish_categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_categories_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `branch_menu_sizes`
--
ALTER TABLE `branch_menu_sizes`
  ADD CONSTRAINT `branch_menu_sizes_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_sizes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_sizes_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_sizes_dish_id_foreign` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_sizes_dish_size_id_foreign` FOREIGN KEY (`dish_size_id`) REFERENCES `dish_sizes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_menu_sizes_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `branch_poses`
--
ALTER TABLE `branch_poses`
  ADD CONSTRAINT `branch_poses_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Constraints for table `branch_recipe`
--
ALTER TABLE `branch_recipe`
  ADD CONSTRAINT `branch_recipe_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_recipe_recipe_id_foreign` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_times`
--
ALTER TABLE `branch_times`
  ADD CONSTRAINT `branch_times_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_times_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_times_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_times_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `brands`
--
ALTER TABLE `brands`
  ADD CONSTRAINT `brands_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `brands_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `brands_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `cashier_machines`
--
ALTER TABLE `cashier_machines`
  ADD CONSTRAINT `cashier_machines_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `cashier_machines_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `cashier_machines_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `cashier_machines_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `cashier_machine_logs`
--
ALTER TABLE `cashier_machine_logs`
  ADD CONSTRAINT `cashier_machine_logs_cashier_machine_id_foreign` FOREIGN KEY (`cashier_machine_id`) REFERENCES `cashier_machines` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `cashier_machine_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `cashier_machine_logs_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `cashier_machine_logs_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `cashier_machine_logs_employee_opening_balance_id_foreign` FOREIGN KEY (`employee_opening_balance_id`) REFERENCES `employee_opening_balances` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `cashier_machine_logs_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `categories_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `categories_modify_by_foreign` FOREIGN KEY (`modify_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_addresses`
--
ALTER TABLE `client_addresses`
  ADD CONSTRAINT `client_addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `colors`
--
ALTER TABLE `colors`
  ADD CONSTRAINT `colors_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `colors_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `colors_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `countries`
--
ALTER TABLE `countries`
  ADD CONSTRAINT `countries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `countries_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `countries_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `dish_addons`
--
ALTER TABLE `dish_addons`
  ADD CONSTRAINT `dish_addons_addon_category_id_foreign` FOREIGN KEY (`addon_category_id`) REFERENCES `addon_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `dish_details`
--
ALTER TABLE `dish_details`
  ADD CONSTRAINT `dish_details_dish_size_id_foreign` FOREIGN KEY (`dish_size_id`) REFERENCES `dish_sizes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dish_discount`
--
ALTER TABLE `dish_discount`
  ADD CONSTRAINT `dish_discount_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dish_discount_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dish_discount_modify_by_foreign` FOREIGN KEY (`modify_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `dish_sizes`
--
ALTER TABLE `dish_sizes`
  ADD CONSTRAINT `dish_sizes_dish_id_foreign` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `domains`
--
ALTER TABLE `domains`
  ADD CONSTRAINT `domains_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_nationality_id_foreign` FOREIGN KEY (`nationality_id`) REFERENCES `nationalities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `f_a_q_s`
--
ALTER TABLE `f_a_q_s`
  ADD CONSTRAINT `f_a_q_s_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `f_a_q_s_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `f_a_q_s_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `kitchen_logs`
--
ALTER TABLE `kitchen_logs`
  ADD CONSTRAINT `kitchen_logs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kitchen_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kitchen_logs_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kitchen_logs_dish_addone_id_foreign` FOREIGN KEY (`dish_addone_id`) REFERENCES `dish_addons` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kitchen_logs_dish_id_foreign` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kitchen_logs_dish_size_id_foreign` FOREIGN KEY (`dish_size_id`) REFERENCES `dish_sizes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kitchen_logs_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kitchen_logs_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kitchen_logs_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kitchen_logs_order_addone_id_foreign` FOREIGN KEY (`order_addone_id`) REFERENCES `order_addons` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kitchen_logs_order_details_id_foreign` FOREIGN KEY (`order_details_id`) REFERENCES `order_details` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kitchen_logs_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kitchen_logs_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kitchen_logs_table_id_foreign` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `leave_settings`
--
ALTER TABLE `leave_settings`
  ADD CONSTRAINT `leave_settings_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `leave_settings_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `logos`
--
ALTER TABLE `logos`
  ADD CONSTRAINT `logos_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `logos_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `logos_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nationalities`
--
ALTER TABLE `nationalities`
  ADD CONSTRAINT `nationalities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `nationalities_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_client_address_id_foreign` FOREIGN KEY (`client_address_id`) REFERENCES `client_addresses` (`id`);

--
-- Constraints for table `order_addons`
--
ALTER TABLE `order_addons`
  ADD CONSTRAINT `order_addons_order_details_id_foreign` FOREIGN KEY (`order_details_id`) REFERENCES `order_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_dish_size_id_foreign` FOREIGN KEY (`dish_size_id`) REFERENCES `dish_sizes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_details_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`);

--
-- Constraints for table `privacy_policies`
--
ALTER TABLE `privacy_policies`
  ADD CONSTRAINT `privacy_policies_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `privacy_policies_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `privacy_policies_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `rates`
--
ALTER TABLE `rates`
  ADD CONSTRAINT `rates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `rates_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `rates_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `return_policies`
--
ALTER TABLE `return_policies`
  ADD CONSTRAINT `return_policies_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `return_policies_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `return_policies_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sliders`
--
ALTER TABLE `sliders`
  ADD CONSTRAINT `sliders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sliders_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sliders_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `terms_and_conditions`
--
ALTER TABLE `terms_and_conditions`
  ADD CONSTRAINT `terms_and_conditions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `terms_and_conditions_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `terms_and_conditions_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_coupons`
--
ALTER TABLE `user_coupons`
  ADD CONSTRAINT `user_coupons_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_coupons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_coupons_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_coupons_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_coupons_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_favorite_dishes`
--
ALTER TABLE `user_favorite_dishes`
  ADD CONSTRAINT `user_favorite_dishes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
