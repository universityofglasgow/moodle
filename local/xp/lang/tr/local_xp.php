<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language file.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activitycompleted'] = 'Aktivite tamamlandı';
$string['badgetheme'] = 'Seviye Rozeti teması';
$string['badgetheme_help'] = 'Rozet teması varsayılan görünümü tanımlar';
$string['currencysign'] = 'Puan Sembolü';
$string['currencysign_help'] = 'Bu ayar ile puanların anlamını değiştirebilirsiniz. Her bir kullanıcının aldığı puanın yanında referans verdiğiniz değer yerine kullanılır _deneyim puanı_.

Örneğin öğrencinin tamamladığı etkinlik ödülü olarak havuç resmi yükleyebilirsiniz.';
$string['currencysignformhelp'] = 'Buraya yüklenen resimler deneyim puanı olarak puanların yanında görüntülenir. Tavsiye edilen resim yüksekliği 18 Pikseldir.';
$string['enablecheatguard'] = 'Hile korumayı etkinleştir';
$string['enablecheatguard_help'] = 'Hile koruma öğrencileri belirli sınırlara ulaştığında ödüllendirilmelerini önler';
$string['enablegroupladder'] = 'Grup ölçeğini etkinleştir';
$string['enablegroupladder_help'] = 'Etkinleştirildiğinde, öğrenciler kurs grubunda yer alan lider tablosunu görebilir. Grup puanları her bir grup üyesinin kazandığı puanlardan hesaplanır. Bu sadece eklenti kurs için kurulmuşsa gerçekleşir, bütün site için değil.';
$string['for2weeks'] = '2 hafta için';
$string['for3months'] = '3 ay için';
$string['gradereceived'] = 'Rütbe alındı';
$string['groupladder'] = 'Grup ölçeği';
$string['keeplogsdesc'] = 'Log\' lar eklenti için önemli rol oynamaktadır. Son kazanılan ödellerin ve diğer şeylerin buluması için hile koruma tarafından kullanılır. Zamanın azaltılması
hangi logların tutulduğu, puanların zaman içinde nasıl dağıldığını etkileyebilir ve dikkatle ele alınmalıdır.';
$string['levelbadges'] = 'Seviye rozetini değiştir';
$string['levelbadges_help'] = 'Rozet temasının görünümünü değiştirmek için bir resim yükleyin';
$string['levelup'] = 'Seviye atla!';
$string['maxpointspertime'] = 'Zaman sınırları içindeki en yüksek puan';
$string['maxpointspertime_help'] = 'Verilen zaman sınırları içerisinde kazanılabilecek en yüksek puan. Değer boş ise ya da "0" sıfıra eşit ise uygulanmaz.';
$string['missingpermssionsmessage'] = 'Bu içeriğe ulaşmak için gerekli izniniz bulunmamaktadır.';
$string['mylevel'] = 'Seviyem';
$string['navgroupladder'] = 'Grup ölçeği';
$string['pluginname'] = 'Level up! Plus';
$string['points'] = 'Puanlar';
$string['privacy:metadata:log'] = 'etkinlik loglarını depolar';
$string['privacy:metadata:log:points'] = 'Etkinlik puanı';
$string['privacy:metadata:log:signature'] = 'Etkinlik bilgileri';
$string['privacy:metadata:log:time'] = 'Gerçekleşen tarih';
$string['privacy:metadata:log:type'] = 'Etkinlik türü';
$string['privacy:metadata:log:userid'] = 'Puan kazanan kullanıcı';
$string['ruleactivitycompletion'] = 'Aktivite tamamlama';
$string['ruleactivitycompletion_help'] = 'Bu koşul aktivite (tamamlama başarız olarak işaretlenmediği sürece) "tamamlandı" olarak işaretlediğinde yerine getirilmiş sayılmaktadır. 

Aktivitelerin tamamlanması için standart Moodle parametrelerine uygun olarak, öğretmenler bir aktiviteyi tamamlanması için gerekli şartları tam olarak kontrol ederler. Bu şartlar kurs içindeki her bir aktivite için tarih, değerlendirme notu gibi parametreler olabilir. Ayrıca bu koşullardan birisi öğrencilerin aktiviteyi manuel olarak tamamlandı işaretlemesini de içermektedir.

Bu şart öğrenciyi sadece tek seferlik ödülü için geçerlidir.';
$string['ruleactivitycompletion_link'] = 'Aktivite_tamamlama';
$string['ruleactivitycompletiondesc'] = 'Aktivite ya da kaynak başarılı bir şekilde tamamlandı';
$string['rulecoursecompletion'] = 'Kurs tamamlama';
$string['rulecoursecompletion_help'] = 'Kurs tamamlandığında bu kural yerine getirilmiş sayılır.

__Not:__ Öğrenciler puanları anlık olarak alamamaktadırlar, Moodle\' ın kurs tamamlama süreci devam ettiğinden biraz vakit almaktadır. Diğer bir deyişle bu işlem cron gerektirmektedir.';
$string['rulecoursecompletion_link'] = 'Course_completion';
$string['rulecoursecompletioncoursemodedesc'] = 'Kurs tamamlandı';
$string['rulecoursecompletiondesc'] = 'Bir kurs tamamlandı';
$string['ruleusergraded'] = 'Notunuz belirlendi';
$string['ruleusergraded_help'] = 'Bu koşul aşağıdaki durumlarda geçerlidir.

* Aktivite değerlendirme notu elde edilmiştir
* Aktiviteyinin geçme notu belirlendi
* Değerlendirme notu oylamalar ile belirlenmemektedir. (Örneğin Forumlarda)
* Değerlendirme notu puan tabanlıdır, ölçek tabanlı değildir.

Bu şart öğrenciyi sadece tek seferlik ödülü için geçerlidir.';
$string['ruleusergradeddesc'] = 'Öğrenci değerlendirme notunu elde etmiştir.';
$string['themestandard'] = 'Standart';
$string['timeformaxpoints'] = 'En yüksek puan için süre';
$string['timeformaxpoints_help'] = 'Kullanıcının belirli bir miktardan daha fazla puan alamadığı süre (saniye)';
$string['uptoleveln'] = '{$a} seviyeye kadar';
$string['visualsintro'] = 'Seviyelerin puanlarını ve görünüşlerini kişiselleştirin.';
