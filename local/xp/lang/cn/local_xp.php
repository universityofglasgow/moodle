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

$string['activitycompleted'] = '活动完成';
$string['afterimport'] = '导入之后';
$string['anonymousgroup'] = '另一团队';
$string['anonymousiomadcompany'] = '另一组织';
$string['anonymousiomaddepartment'] = '另一部门';
$string['badgetheme'] = '榜单徽章主题';
$string['badgetheme_help'] = '徽章主题使用默认主题';
$string['categoryn'] = '目录: {$a}';
$string['clicktoselectcourse'] = '单击选择一个课程';
$string['clicktoselectgradeitem'] = '单击选择一个项目的成绩';
$string['courseselector'] = '课程选择';
$string['csvfieldseparator'] = 'CSV 的字段分隔符';
$string['csvfile'] = 'CSV 文件';
$string['csvfile_help'] = 'CSV 文件必须包含 __user__ 和 __points__. __message__ 列是可选的，且在启用通知时可用。请注意，__ user__ 列可理解用户 IDs，邮件地址和用户名。';
$string['csvisempty'] = 'CSV 文件是空的.';
$string['csvline'] = 'CSV 线';
$string['csvmissingcolumns'] = 'CSV 错误栏目: {$a}.';
$string['currencysign'] = '积分的符号';
$string['currencysign_help'] = '可以在此处更改积分的说明。将显示在每个用户的积分边，以替代对_experience points_ 的引用。


例如您可以上传图像胡萝卜，用户因成就而获得奖励的胡萝卜。';
$string['currencysignformhelp'] = '上传的图像将显示在积分旁边，替代可参考的积分。推荐图像高度为 18 像素。';
$string['currentpoints'] = '当前积分';
$string['displaygroupidentity'] = '显示团队身份';
$string['enablecheatguard'] = '启用防欺诈';
$string['enablecheatguard_help'] = '达到一定的限制，防欺诈可阻止学生获取奖励。

[更多](https://docs.levelup.plus/xp/docs/getting-started/cheat-guard?ref=localxp_help)';
$string['enablegroupladder'] = '启用团队条装图';
$string['enablegroupladder_help'] = '启用后学生可以查看课程组的排行榜。组积分是根据组内的成员所累积的积分来计算的。当前仅在每门课程都使用插件时才适用，且不适用于整个站点。';
$string['errorunknowncourse'] = '报错: 未知的课程';
$string['errorunknowngradeitem'] = '报错: 未知的成绩项目';
$string['filtergradeitems'] = '过滤成绩项目';
$string['for2weeks'] = '2 星期';
$string['for3months'] = '3 月';
$string['gradeitemselector'] = '项目成绩选择';
$string['gradeitemtypeis'] = '成绩是 {$a}';
$string['gradereceived'] = '收到的成绩';
$string['gradesrules'] = '成绩的规则';
$string['gradesrules_help'] = '以下规则设定了学生在收获得成绩后如何赢得的积分。

学生将获得与其成绩同等的积分。
成绩为5/10和5/100，都会给学生5分。当学生有多次成绩获得时，他们获得的积分将取自其中最高的那个成绩。
学生的积分永远不会被夺走，且负的分数会被忽略。

举例：爱丽丝提交作业后获得40/100的分数。在 _Level Up XP_ 中，爱丽丝通过成绩获得 40 积分。
爱丽丝重新提交她的作业，但是这次她的成绩降低到 25/100。爱丽丝在_Level Up XP_ 中的积分不变。
爱丽丝(Alice)的最后一次尝试得分为 60/100，她在_Level Up XP_ 中将额外获得 20 积分，她最后的总积分为 60.

[更多 _Level Up XP_ 文档](https://docs.levelup.plus/xp/docs/how-to/grade-based-rewards?ref=localxp_help)';
$string['groupanonymity'] = '匿名者';
$string['groupanonymity_help'] = '此处设置控制参与者是否可以看到其他团队的名称。';
$string['groupladder'] = '团队梯形图';
$string['groupladdercols'] = '梯形图栏目';
$string['groupladdercols_help'] = '此设置确定哪些列显示在排名和姓名之侧。

__Points__ 列显示团队的积分数。
根据 _Ranking strategy_ 的选择，该值可能已统计。

__Progress__ 列显示团队中目标为最终成绩的全体成员的总进度。
换句话说，当所有团队成员都处于最高水平时，进度只能达到100％。留意数量提示，当团队不平衡且积分未统计时，进度条旁边显示的剩余积分可能会造成混淆。因为拥有更多成员的团队将拥有比其他成员更多的积分，即使他们的进度可能相似。

单击时按 CTRL 或 CMD 键以选择多个列，或取消已选定的列。';
$string['groupladdersource'] = '学生选用团队合作';
$string['groupladdersource_help'] = '团队梯形图显示了学生的积分排行榜。
您选择的值确定 _Level Up XP_ 将使用学生一起分组的方式。
设置为 _Nothing_ 时，团队梯形图将不可用。

要限制排行榜中出现的 _Course groups_ ，您可以创建一个包含相关分组的新组，然后在课程设置中将此分组设置为 _Default grouping_ .';
$string['groupname'] = '团队名称';
$string['grouporderby'] = '排名策略';
$string['grouporderby_help'] = '确定团队排名的基础是什么.

当设置为 __Points__, 团队将根据其成员的积分总和进行排名。

当设置为 __Points (有偿)__时，成员数少于其他成员的团队积分将使用该团队成员的平均积分来补偿。例如，如果一个团队缺少 3 名成员，则他们获得的补偿积分等于成员平均数的三倍。这将创建一个公平的排名，所有团队都有均等的机会。


当设置为 __Progress__ , 团队将根据其向所有成员达到最终水平的总体进度进行排名，而不会补偿其积分。当团队人数不均等时，例如某些团队成员比其他团队多时，您可能需要使用 _Progress_ .';
$string['grouppoints'] = '积分';
$string['grouppointswithcompensation'] = '积分 (有偿)';
$string['groupsourcecohorts'] = '相似的组';
$string['groupsourcecoursegroups'] = '课程组';
$string['groupsourceiomadcompanies'] = 'IOMAD 组织';
$string['groupsourceiomaddepartments'] = 'IOMAD 部门';
$string['groupsourcenone'] = '无, 梯形图禁用';
$string['hidegroupidentity'] = '隐藏团队身份';
$string['importcsvintro'] = '用下面的表格从 CSV 文件导入积分。导入可用于 _increase_ 学生的分数，或覆盖它们的值。注意，导入 __does not__ 使用与导出报告相同的格式。所需的格式描述在 [documentation]({$a->docsurl}), 另有一个示例文件 [here]({$a->sampleurl}).';
$string['importpoints'] = '导入积分';
$string['importpointsaction'] = '积分导入动作';
$string['importpointsaction_help'] = '确定如何处理CSV文件中找到的积分。

**设为总分**

积分将覆盖学生的当前分数，成为新的分数。不会通知用户，且日志中不会有任何条目。

**增加**

积分代表奖励学生的分数。启用后，来自CSV文件的_message_ 可选通知将发送给收件人。 _Manual award_ 条目也将添加到日志中。';
$string['importpreview'] = '导入预览';
$string['importpreviewintro'] = '这是预览图，展示了所有要导入的记录中前 {$a} 条记录。请检查并确认何时准备导入所有内容。';
$string['importresults'] = '导入结果';
$string['importresultsintro'] = '成功 **导入 {$a->successful} 条目** 之总数 **{$a->total}**. 如果某些条目无法导入，则详细信息将显示在下面。';
$string['importsettings'] = '导入设置';
$string['increaseby'] = '增加';
$string['increaseby_help'] = '奖励学生的积分数';
$string['increasemsg'] = '可选信息';
$string['increasemsg_help'] = '提供消息后，它将被添加到通知中。';
$string['invalidpointscannotbenegative'] = '积分数不能为负.';
$string['keeplogsdesc'] = '插件中的日志起着重要作用。它用于防止作弊，可查询最近的奖励或其他。减少用于保留日志的时间，将会影响积分随时间的分布方式，请应谨慎处理。';
$string['levelbadges'] = '上榜徽章优先';
$string['levelbadges_help'] = '上传图像以覆盖徽章主题已提供的设计。';
$string['levelup'] = '上榜!';
$string['manualawardnotification'] = '您已获得 {$a->points} 积分 {$a->fullname}.';
$string['manualawardnotificationwithcourse'] = '您已获得 {$a->points} 积分 {$a->fullname} 在课程 {$a->coursename}.';
$string['manualawardsubject'] = '您已获得 {$a->points} 积分!';
$string['manuallyawarded'] = '手动授予';
$string['maxn'] = '最大: {$a}';
$string['maxpointspertime'] = '时间点内的最大积分';
$string['maxpointspertime_help'] = '在给定的时间范围内可以赚取的最多的积分。当该值为空或零时，则不适用。';
$string['messageprovider:manualaward'] = '上榜! 积分手动奖励';
$string['missingpermssionsmessage'] = '您无权限访问此内容。';
$string['mylevel'] = '我的等级';
$string['navgroupladder'] = '团队梯形图';
$string['pluginname'] = '上榜! 更强';
$string['points'] = '积分';
$string['previewmore'] = '预览更多';
$string['privacy:metadata:log'] = '存储事件日志';
$string['privacy:metadata:log:points'] = '活动获得的积分';
$string['privacy:metadata:log:signature'] = '一些事件的数据';
$string['privacy:metadata:log:time'] = '发生的日期';
$string['privacy:metadata:log:type'] = '活动类型';
$string['privacy:metadata:log:userid'] = '获得积分的用户';
$string['progressbarmode'] = '显示进度条';
$string['progressbarmode_help'] = '设置为 _The next level_, 进度条将显示用户向下一级别的进度。

设置为 _The ultimate level_, 进度条将指示用户达到的最终级别的进度百分比。

在任何一种情况下，当达到最后一个级别时，进度条将保持为满。';
$string['progressbarmodelevel'] = '下一个等级';
$string['progressbarmodeoverall'] = '最终等级';
$string['ruleactivitycompletion'] = '活动完成';
$string['ruleactivitycompletion_help'] = '若将活动标记为已完成，即可符合此条件。只要未将完成标记为失败即可。

根据标准的 Moodle 活动完成设置，教师可完全控制需要 _complete_ 一项活动的条件。可针对课程中的每个活动分别设置这些日期，成绩等依据… 也可以允许学生手动标记活动完成。

这种情况只会奖励一次学生。';
$string['ruleactivitycompletion_link'] = 'Activity_completion';
$string['ruleactivitycompletiondesc'] = '活动或资源已成功完成';
$string['ruleactivitycompletioninfo'] = '当学生完成活动或资源时, 此条件匹配';
$string['rulecmname'] = '活动名称';
$string['rulecmname_help'] = '当事件发生在按指定命名的活动中时，将满足此条件。

备注：

- 比较不区分大小写。
空值永远不会匹配。
 当活动名称包括**contains**. [multilang](https://docs.moodle.org/en/Multi-language_content_filter)';
$string['rulecmnamedesc'] = '活动名称 {$a->compare} \'{$a->value}\'.';
$string['rulecmnameinfo'] = '指定必须在其中执行操作的活动或资源的名称。';
$string['rulecourse'] = '课程';
$string['rulecourse_help'] = '当事件在指定的课程中发生时，即可符合此条件。

仅当插件用于整个站点时才可用。如果按课程使用插件，则此条件无效。';
$string['rulecoursecompletion'] = '课程完成';
$string['rulecoursecompletion_help'] = '学生完成课程后，即可符合此规则。

__Note:__ 学生不会立即获得他们的积分，Moodle 需要一些时间来处理完成的课程。换言之，需要一次 _cron_ 的运行。';
$string['rulecoursecompletion_link'] = 'Course_completion';
$string['rulecoursecompletioncoursemodedesc'] = '课程已完成';
$string['rulecoursecompletiondesc'] = '一门课程已完成';
$string['rulecoursecompletioninfo'] = '当学生完成课程时此条件匹配。';
$string['rulecoursedesc'] = '该课程是: {$a}';
$string['rulecourseinfo'] = '此条件要求在特定课程中采取行动。';
$string['rulegradeitem'] = '特定等级项目';
$string['rulegradeitem_help'] = '当指定的项目获得成绩时，符合此条件。';
$string['rulegradeitemdesc'] = '等级项目是 \'{$a->gradeitemname}\'';
$string['rulegradeitemdescwithcourse'] = '等级项目是 : \'{$a->gradeitemname}\' in \'{$a->coursename}\'';
$string['rulegradeiteminfo'] = '此条件与特定项目的收获成绩相匹配。';
$string['rulegradeitemtype'] = '等级类型';
$string['rulegradeitemtype_help'] = '当等级项目为所需类型时，符合此条件。选择活动类型后，任何源自该活动类型的成绩都会匹配。';
$string['rulegradeitemtypedesc'] = '成绩是 \'{$a}\'';
$string['rulegradeitemtypeinfo'] = '当项目成绩属于所需类型时，此条件匹配。';
$string['ruleusergraded'] = '收获成绩';
$string['ruleusergraded_help'] = '以下情况下符合此条件:

* 在一项活动中获得了成绩
* 活动指定及格分数
* 成绩达到及格分数
* 成绩 _not_ 基于评分 (例如在论坛中)
* 成绩是基于积分的，而不是基于获得分数

这种情况只会奖励1 次学生。';
$string['ruleusergradeddesc'] = '学生获得的及格分数';
$string['sendawardnotification'] = '发送奖励通知';
$string['sendawardnotification_help'] = '启用后，学生将会收到一条通知，告知他们已获得积分。该消息将包含您的姓名，分数和课程名称（如果有）。';
$string['shortcode:xpteamladder'] = '显示团队梯形图的一部分。';
$string['shortcode:xpteamladder_help'] = '默认情况下，围绕当前用户的团队的梯形图将显示。
```
[xpteamladder]
```
要显示前 5 名的团队而不是与用户所毗邻的团队，请设置参数`top`. 您可以给 `top` 设置一个值来显示团队的数量，例如: `top=20`.

```
[xpteamladder top]
[xpteamladder top=15]
```
若要显示更多结果，则在表格下方自动显示一个完整梯形图的链接，如果您不想显示此类链接，请添加参数 `hidelink`.

```
[xpteamladder hidelink]
```

默认情况下，该表不包括显示进度条的进度列。如果在梯形图设置的其他列中选择了该列，则可以使用参数 `withprogress` 来显示它。

```
[xpteamladder withprogress]
```

请注意，当用户属于多个团队时，该插件将仅使用排名最高的团队作为参考。';
$string['studentsearnpointsforgradeswhen'] = '在以下情况下，学生可获得成绩：';
$string['themestandard'] = '标准';
$string['theyleftthefollowingmessage'] = '他们留下的信息:';
$string['timeformaxpoints'] = '最久的时间范围. 积分';
$string['timeformaxpoints_help'] = '在某时间范围内(以秒为单位)，用户不能接收超过一定数量的积分';
$string['unabletoidentifyuser'] = '无法认证用户.';
$string['unknowngradeitemtype'] = '未知类型 ({$a})';
$string['uptoleveln'] = '升级 {$a}';
$string['visualsintro'] = '自定义榜单和积分的外观.';
