-- rollback file from lucas-nuevo@2015-03-15 13:02:25

update tau_translations set content='O\'Hara Straße' where lang='de' and t_group='tau_96faeda' and item='tau_96faeda_replace_full_filename' limit 1;


-- &|6.28@tau&

update tau_translations set content='La calle de O\'Haras' where lang='es' and t_group='tau_96faeda' and item='tau_96faeda_replace_full_filename' limit 1;


-- &|6.28@tau&

update tau_translations set content='Paketen Granden und Corten' where lang='de' and t_group='tau_96faeda' and item='tau_96faeda_replace_content_1' limit 1;


-- &|6.28@tau&

update tau_translations set content='Lukanen2' where lang='de' and t_group='tau_96faeda' and item='tau_96faeda_replace_testing_a' limit 1;


-- &|6.28@tau&

delete from tau_translations where lang='de' and t_group='tau_96faeda' and item='tau_96faeda_replace_copyright' limit 1;


-- &|6.28@tau&

delete from migrates where name='20150315_130225_translations_tau_96faeda' limit 1;

