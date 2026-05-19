# Slate for WP — Migration Plan & Initial Scaffold

此主题为对 estudiopatagon/slate-for-ghost 的 WordPress 复刻项目的初始提交（脚手架 + 迁移计划）。后续提交将逐步移植样式、模板与交互以实现像素级复刻。

迁移计划（高层）
1. 资源清单与许可
   - 源仓库许可：MIT（已确认）。
   - 需要单独核验的第三方资源（字体、图像、图标、外部库）。

2. 模板映射（Ghost -> WordPress）
   - default.hbs -> header.php + footer.php + functions.php (全局部分)
   - index.hbs -> index.php / home.php
   - post.hbs -> single.php
   - page.hbs -> page.php
   - tag.hbs/author.hbs -> taxonomy/tag.php / author.php
   - partials/* -> template-parts/*

3. 功能清单
   - 文章列表、单篇、页面、标签页、作者页、搜索、分页
   - 菜单、侧边栏、小工具区域
   - Customizer 中的 logo、颜色设置
   - 可能需要的 JS 交互（导航、阅读进度等）

4. 开发流程与交付
   - 本分支：replica/slate-for-wp
   - 我将逐步提交：样式迁移、模板迁移、交互脚本、测试与 bug 修复。

安装与测试（临时）
- 将本主题目录放入 WordPress 安装的 wp-content/themes/ 下，激活主题并检查页面布局与样式。

注意事项
- 本提交为初始脚手架：主要负责目录结构、基础模板与资源引入。完整像素级复刻将在后续提交中完成。
- 如果有禁止使用的源资源，请在 issues 中指出，我将替换或移除。

作者与许可
- 原主题：estudiopatagon/slate-for-ghost（MIT）。
- 本移植工作遵守原始许可并在 README 中列出资源来源。
