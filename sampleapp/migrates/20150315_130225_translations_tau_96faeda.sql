-- lucas-nuevo@2015-03-15 13:02:25

update tau_translations set content='O\'Hara Straßen' where lang='de' and t_group='tau_96faeda' and item='tau_96faeda_replace_full_filename' limit 1;


-- &|6.28@tau&

update tau_translations set content='La calle de O\'Hara' where lang='es' and t_group='tau_96faeda' and item='tau_96faeda_replace_full_filename' limit 1;


-- &|6.28@tau&

update tau_translations set content='Paketen Granden und Gorden' where lang='de' and t_group='tau_96faeda' and item='tau_96faeda_replace_content_1' limit 1;


-- &|6.28@tau&

update tau_translations set content='Lukanen' where lang='de' and t_group='tau_96faeda' and item='tau_96faeda_replace_testing_a' limit 1;


-- &|6.28@tau&

insert ignore into tau_translations(lang,t_group,item,content) values 
('de','tau_96faeda','tau_96faeda_replace_copyright','Lucas');


