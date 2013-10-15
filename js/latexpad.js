var filesdata = {};
function latexpad_update(){
	$.post(
		OC.filePath('latexpad','ajax','updates.php'),
		filesdata,
		function(result){
			if (result.status == 'success') {
				newfilesdata = result.data;
				src = OC.filePath('files', 'ajax', 'download.php') + '?dir=' + encodeURIComponent(newfilesdata.dir+'/build') + '&ts=' + new Date().getTime() + '&files=';
				for(i=1;filesdata['page-'+i] || newfilesdata['page-'+i];i++){
					if(i>10)
						break;
					
					if(filesdata['page-'+i]){
						if(newfilesdata['page-'+i]){
							if(filesdata['page-'+i]!=newfilesdata['page-'+i]){
								$('#page-'+i).attr("src",src+encodeURIComponent('page-'+i+'.png'))
									.fadeOut(500).fadeIn(500)
									.fadeOut(500).fadeIn(500)
									.fadeOut(500).fadeIn(500);
							}
						}else{
							$('#page-'+i).remove();
						}
					}else{
						$('#latexpad_preview').append('<img id="page-'+i+'" src="'+src+encodeURIComponent('page-'+i+'.png')+'">');
					}
				}
				
				if((!filesdata.log) || filesdata.log!=newfilesdata.log){
					var data = $.getJSON(
						OC.filePath('files_texteditor', 'ajax', 'loadfile.php'),
						{file: newfilesdata.logfilename, dir: newfilesdata.dir+'/build'},
						function (result) {
							if (result.status == 'success') {
								$('#latexpad_log').scrollTop(0);
								$('#latexpad_log pre').text();
								$('#latexpad_log pre').text(result.data.filecontents);
								$('#latexpad_log').animate({ scrollTop: $('#latexpad_log')[0].scrollHeight}, 1000);
							} else {
								OC.dialogs.alert(result.data.message, t('files_texteditor', 'An error occurred!'));
							}
						}
					);
				}
				$.extend(filesdata, result.data);
			} else {
				OC.dialogs.alert(result.data.message, 'Error');
			}
		}
	);
	setTimeout(latexpad_update,2000);
}
$(document).ready(function(){
	if($("#latexpad_pad").length!==0){
		$.extend(filesdata,{dir:$('#dir').val()});
		latexpad_update();
		$("#latexpad_compile").click(function(){
			var data = $.getJSON(
				OC.filePath('latexpad', 'ajax', 'compile.php'),
				{padID: $('#padID').val()},
				function (result) {
				}
			);
		});
// 		alert(OC.filePath('latexpad','ajax','updates.php'));
// 		alert($('#dir').val());
	}
		
	if(typeof FileActions!=='undefined'){
		FileActions.register('application/x-tex',t('latexpad','Edit'), OC.PERMISSION_READ, '',function(filename){
			//alert($('#dir').val()+" - "+filename);
			var params = {
				dir: encodeURIComponent($('#dir').val()),
				file: encodeURIComponent(filename)
			};
			window.location=OC.Router.generate('latexpad_edit', params);
			//pad=new FileToPad($('#dir').val(),filename);
		});
		FileActions.setDefault('application/x-tex',t('latexpad','Edit'));


		getMimeIcon('dir',function(icon){
			$('<li><p>'+t('latexpad','LaTeX Project')+'</p></li>')
			.data('type','latexProject')
			.appendTo('#new>ul')
			.css('background-image', 'url(' + icon + ')')
			.off('click')
			.click(function() {
				if($(this).children('p').length==0){
					return;
				}

				$('#new li').each(function(i,element){
					if($(element).children('p').length==0){
						$(element).children('form').remove();
						$(element).append('<p>'+$(element).data('text')+'</p>');
					}
				});

				var type=$(this).data('type');
				var text=$(this).children('p').text();
				$(this).data('text',text);
				$(this).children('p').remove();
				var form=$('<form></form>');
				var input=$('<input>');
				form.append(input);
				$(this).append(form);
				input.focus();
				form.submit(function(event){
					event.stopPropagation();
					event.preventDefault();
					var newname=input.val();
					if (!Files.isFileNameValid(newname)) {
						return false;
					} else if( type == 'latexProject' && $('#dir').val() == '/' && newname == 'Shared') {
						OC.Notification.show(t('files','Invalid folder name. Usage of \'Shared\' is reserved by Owncloud'));
						return false;
					}
					if (FileList.lastAction) {
						FileList.lastAction();
					}
					var name = getUniqueName(newname);
					if (newname != name) {
						FileList.checkName(name, newname, true);
						var hidden = true;
					} else {
						var hidden = false;
					}
					switch(type){
						case 'latexFile':
							$.post(
								OC.filePath('files','ajax','newfile.php'),
								{dir:$('#dir').val(),filename:name},
								function(result){
									if (result.status == 'success') {
										var date=new Date();
										FileList.addFile(name,0,date,false,hidden);
										var tr=$('tr').filterAttr('data-file',name);
										tr.attr('data-mime','text/plain');
										tr.attr('data-id', result.data.id);
										getMimeIcon('text/plain',function(path){
											tr.find('td.filename').attr('style','background-image:url('+path+')');
										});
									} else {
										OC.dialogs.alert(result.data.message, 'Error');
									}
								}
							);
							break;
						case 'latexProject':
							$.post(
								OC.filePath('files','ajax','newfolder.php'),
								{dir:$('#dir').val(),foldername:name},
								function(result){
									if (result.status == 'success') {
										$.post(
											OC.filePath('files','ajax','newfile.php'),
											{dir:$('#dir').val()+name,filename:'.config',content:'command=pdflatex\nmaster='+name+'.tex'},
											function(result){
												if (result.status != 'success') {
													OC.dialogs.alert(result.data.message, 'Error');
												}
											}
										);
										$.post(
											OC.filePath('files','ajax','newfile.php'),
											{dir:$('#dir').val()+name,filename:name+'.tex'},
											function(result){
												if (result.status != 'success') {
													OC.dialogs.alert(result.data.message, 'Error');
												}
											}
										);
										$.post(
											OC.filePath('files','ajax','newfolder.php'),
											{dir:$('#dir').val()+name,foldername:'build'},
											function(result){
												if (result.status != 'success') {
													OC.dialogs.alert(result.data.message, 'Error');
												}
											}
										);
										var date=new Date();
										FileList.addDir(name,0,date,hidden);
										var tr=$('tr').filterAttr('data-file',name);
										tr.attr('data-id', result.data.id);
									} else {
										OC.dialogs.alert(result.data.message, 'Error');
									}
								}
							);
							break;
					}
					var li=form.parent();
					form.remove();
					li.append('<p>'+li.data('text')+'</p>');
					$('#new>a').click();
				});
			});
		});
	}
});
