
# 1.2升级到2.0遗漏的数据
INSERT INTO `main_setting` ( `_key`, `title`, `module`, `order_weight`, `_value`, `default_value`, `format`, `form_input_type`, `form_optional_value`, `description`) VALUES
( 'allow_user_reg', '允许用户注册', 'basic', 0, '1', '1', 'int', 'radio', '{\"1\":\"开启\",\"0\":\"关闭\"}', '如关闭，则用户无法注册系统用户');
# 增加对ldap的支持
ALTER TABLE `user_main` CHANGE `from_schema` `schema_source` VARCHAR(12) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'inner' COMMENT '用户数据源: inner ldap wechat weibo github等';

# 增加：管理甘特图和事项分解设置的权限项 @todo 升级脚本要增加管理员和po角色的权限
INSERT INTO `project_permission` (`id`, `parent_id`, `name`, `description`, `_key`) VALUES
(10907, 0, '管理甘特图', '是否拥有权限操作甘特图中的事项和设置', 'ADMIN_GANTT'),
(10908, 0, '事项分解设置', '是否拥有权限修改事项分解的设置', 'MIND_SETTING');

# 甘特图设置增加上班日配置
ALTER TABLE `project_gantt_setting` ADD `work_dates` JSON NOT NULL AFTER `hide_issue_types`;

# 是否在事项列表显示分类
ALTER TABLE `project_main` ADD `is_display_issue_catalog` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否在事项列表显示分类' AFTER `issue_update_time`;

# 修改设置标题长度
ALTER TABLE `main_setting` CHANGE `title` `title` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题';

# 创建项目分类表
CREATE TABLE `project_catalog_label` (
  `id` int(11) UNSIGNED NOT NULL,
  `project_id` int(11) NOT NULL,
  `name` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label_id_json` varchar(5000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `font_color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'blueviolet' COMMENT '字体颜色',
  `description` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `order_weight` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目的分类定义';
ALTER TABLE `project_catalog_label`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `project_id_2` (`project_id`,`order_weight`);

ALTER TABLE `project_catalog_label`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
COMMIT;



