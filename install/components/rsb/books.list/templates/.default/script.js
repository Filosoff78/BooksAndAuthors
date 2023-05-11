BX.ready(function (){
    BX.SidePanel.Instance.bindAnchors({
        rules: [
            {
                condition: ['/rsb/books/books_edit.php'],
                options: sidePanelParams
            }
        ]
    });

    BX.addCustomEvent('Bitrix24.Slider:onMessage', function (event, params) {
        if(params.hasOwnProperty('booksUpdate') && params.booksUpdate) {
            const booksGrid = BX.Main.gridManager.getInstanceById(gridId);
            if (booksGrid) {
                booksGrid.reload();
            }
        }
    })
});
