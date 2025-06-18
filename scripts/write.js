document.addEventListener('DOMContentLoaded', () => {
    const addButton = document.getElementById('add-element-btn');
    const chooser = document.getElementById('element-chooser');
    const form = document.getElementById('article-form');
    const body = document.getElementById('article-body');
    const submitPublishedBtn = document.getElementById('submit-article-published-btn');
    const submitUnpublishedBtn = document.getElementById('submit-article-unpublished-btn');
    const apiEndPoint = document.querySelector('input[name="api-endpoint"]').value;

    const alreadyLoadedBlocks = document.querySelectorAll('.article-item');
    let blockCounter = alreadyLoadedBlocks.length;

    const paragraphs = document.querySelectorAll('.paragraph');

    paragraphs.forEach((paragraph) => {
        paragraph.addEventListener('input', () => {
        paragraph.style.height = 'auto';
            paragraph.style.height = paragraph.scrollHeight + 'px';
        });

        paragraph.style.height = 'auto';
        paragraph.style.height = paragraph.scrollHeight + 'px';
    })

    addButton.addEventListener('click', () => {
        chooser.style.display = (chooser.style.display === 'none') ? 'block' : 'none';
    });

    chooser.addEventListener('click', (event) => {
        if (!event.target.dataset.type) return;

        const type = event.target.dataset.type;
        const block = createBlock(type);
        body.appendChild(block);

        chooser.style.display = 'none';
    });

    function createBlock(type) {
        blockCounter++;

        const wrapper = document.createElement('div');
        wrapper.classList.add('article-item');
        wrapper.id = `article-id-${blockCounter}`;
        wrapper.dataset.type = type;
        wrapper.style.position = 'relative';

        switch (type) {
            case 'paragraph': {
                const textarea = document.createElement('textarea');
                textarea.name = 'content[]';
                textarea.rows = 1;
                textarea.style.overflow = 'hidden';
                textarea.style.resize = 'none';
                textarea.className = 'paragraph';

                textarea.addEventListener('input', () => {
                    textarea.style.height = 'auto';
                    textarea.style.height = textarea.scrollHeight + 'px';
                });

                const label = document.createElement('label');
                label.innerText = 'Paragraph:';
                label.appendChild(document.createElement('br'));
                label.appendChild(textarea);

                wrapper.appendChild(label);
                break;
            }

            case 'quote':
                wrapper.innerHTML += '<label>Quote:<br><input type="text" name="content[]" /></label>';
                break;

            case 'subtitle':
                wrapper.innerHTML += '<label>Subtitle:<br><input type="text" name="content[]" /></label>';
                break;

            case 'image':
                wrapper.innerHTML += '<label>Image:<br><input type="file" name="content[]" accept="image/*" /></label>';
                break;
            
            case 'table':
                wrapper.innerHTML += `
                    <label>Rows:<input type="number" name="table-rows" min="1" /></label>
                    <label>Columns:<input type="number" name="table-columns" min="1" /></label>
                    <button type="button" id="create-table-btn" onClick="generateTableInputs(this.parentNode);">Create</button>
                `;
                const elementAddBtn = document.getElementById('add-element-btn');
                elementAddBtn.style.display = 'none';
                break;
            
            case 'youtube_video':
                wrapper.innerHTML += `<label>Youtube link:<input type="text" name="youtube-link"/></label>`;
                break;
        }

        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = 'X';
        deleteBtn.style.display = 'none';
        wrapper.appendChild(deleteBtn);
        
        deleteBtn.addEventListener('click', () => {
            wrapper.remove();
        });

        wrapper.addEventListener('mouseenter', () => {
            deleteBtn.style.display = 'inline';
        });

        wrapper.addEventListener('mouseleave', () => {
            deleteBtn.style.display = 'none';
        });

        return wrapper;
    }

    submitPublishedBtn.addEventListener('click', async => {submitArticle(true)});
    submitUnpublishedBtn.addEventListener('click', async => {submitArticle(false)});

    async function submitArticle(isPublished) {
        const title = form.querySelector('input[name="title"]').value.trim();
        const shortDesc = form.querySelector('input[name="short-description"]').value.trim();
        const coverFile = form.querySelector('input[name="cover_photo"]').files[0];
        const uuid = document.querySelector('input[name="uuid"]')?.value;

        if (!title || !shortDesc) {
            alert("Please fill out title, short description, and upload a cover image.");
            return;
        }

        if (!uuid && !coverFile) {
            alert("Please fill out title, short description, and upload a cover image.");
            return;
        }

        const article = {
            title,
            short_description: shortDesc,
            is_published: isPublished,
            article_elements: []
        };

        const formData = new FormData();

        var coverWebP;
        if (coverFile !== undefined) {
            coverWebP = await convertToWebP(coverFile);
            formData.append('cover_photo', coverWebP, 'cover.webp');
        }

        if (uuid) {
            formData.append('uuid', uuid);
        }

        const blocks = body.querySelectorAll('.article-item');
        let imageIndex = 0;

        for (let block of blocks) {
            const type = block.dataset.type;

            if (type === 'paragraph' || type === 'quote' || type === 'subtitle') {
                const input = block.querySelector('textarea, input[type="text"]');
                if (input && input.value.trim()) {
                    article.article_elements.push({
                        type,
                        value: input.value.trim()
                    });
                }
            }

            if (type === 'image') {
                const fileInput = block.querySelector('input[type="file"]');
                const file = fileInput?.files?.[0];
                if (file) {
                    const webpBlob = await convertToWebP(file);
                    const filename = `article_item_${imageIndex}.webp`;
                    formData.append('images[]', webpBlob, filename);
                    article.article_elements.push({
                        type: 'image',
                        src: filename
                    });
                    imageIndex++;
                }
            }

            if (type === 'table') {
                const tableWrapper = block.querySelector('.generated-table');
                if (!tableWrapper) return;

                const titleInput = tableWrapper.querySelector('input[name="title"]');
                const tableTitle = titleInput?.value?.trim() ?? '';

                const cellInputs = tableWrapper.querySelectorAll('input[id^="table-"][id*="-cell-"]');

                const tableData = {};

                cellInputs.forEach(input => {
                    const match = input.id.match(/table-\d+-cell-(\d+)-(\d+)/);
                    if (!match) return;

                    const row = parseInt(match[1]);
                    const col = parseInt(match[2]);

                    if (!tableData[row]) tableData[row] = [];
                    tableData[row][col] = input.value;
                });

                const rowsArray = Object.keys(tableData)
                    .sort((a, b) => a - b)
                    .map(row => tableData[row]);

                article.article_elements.push({
                    type: 'table',
                    title: tableTitle,
                    data: rowsArray
                });
            }

            if (type === 'youtube_video') {
                const input = block.querySelector('textarea, input[type="text"]');
                const urlInputValue = input.value;
                if (!urlInputValue) 
                    continue;

                let url;
                try {
                    url = new URL(urlInputValue);
                }
                catch (e) {
                    alert('Please insert a valid youtube url');
                    return;
                }

                const validHostnames = [
                    'www.youtube.com',
                    'm.youtube.com',
                    'youtu.be'
                ]
                if (!validHostnames.includes(url.hostname)) {
                    alert('Please insert a valid youtube url');
                    return;
                }

                let videoId;
                if (url.hostname === 'youtu.be') {
                    videoId = url.pathname.slice(1);
                    if (!videoId) {
                        alert('Please insert a valid youtube url');
                        return;
                    }
                }
                else {
                    videoId = url.searchParams.get('v');
                    if (!videoId) {
                        alert('Please insert a valid youtube url');
                        return;
                    }
                }

                article.article_elements.push({
                    type: 'youtube_video',
                    video_id: videoId
                });
            }
        }

        formData.append('metadata', JSON.stringify(article));

        const response = await fetch(apiEndPoint, {
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            alert("Article submitted successfully!");
            window.location.href = '../users/dashboard.php';
        } else {
            alert(`Error submitting article.`);
        }
    }

    async function convertToWebP(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            const reader = new FileReader();
            reader.onload = () => {
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    canvas.toBlob(blob => {
                        if (blob) resolve(blob);
                        else reject(new Error("Failed to convert image to WebP"));
                    }, 'image/webp', 0.9);
                };
                img.src = reader.result;
            };
            reader.readAsDataURL(file);
        });
    }
});

function generateTableInputs(wrapper) {
    if (!wrapper || !wrapper.classList.contains('article-item')) {
        console.error('generateTableInputs: Invalid wrapper element.');
        return;
    }

    const articleId = wrapper.id.replace('article-id-', '');

    const rows = parseInt(wrapper.querySelector('input[name="table-rows"]').value, 10);
    const cols = parseInt(wrapper.querySelector('input[name="table-columns"]').value, 10);

    if (isNaN(rows) || isNaN(cols) || rows < 1 || cols < 1) {
        alert('Please enter valid numbers for rows and columns.');
        return;
    }

    const labels = wrapper.querySelectorAll('label');
    labels.forEach(element => {
        element.remove();
    });
    const fields = wrapper.querySelectorAll('input');
    fields.forEach(element => {
        element.remove();
    });
    const button = document.getElementById('create-table-btn');
    button.remove();

    const table = document.createElement('div');
    table.className = 'generated-table';

    table.innerHTML = `
        <label>Table title: <input type="text" name="title" required></label>
    `;

    for (let row = 0; row < rows; row++) {
        const rowDiv = document.createElement('div');
        rowDiv.style.display = 'flex';
        rowDiv.className = "table-row";
        for (let col = 0; col < cols; col++) {
            const input = document.createElement('input');
            input.type = 'text';
            input.name = `table-${articleId}-cell-${row}-${col}`;
            input.id = `table-${articleId}-cell-${row}-${col}`;
            input.placeholder = `${row + 1},${col + 1}`;
            input.style.margin = '2px';
            rowDiv.appendChild(input);
        }
        table.appendChild(rowDiv);
    }

    const addRowBtn = document.createElement('button');
    addRowBtn.id = `table-${articleId}-add-row`;
    addRowBtn.textContent = "Add Row";
    addRowBtn.type = "button";
    addRowBtn.onclick = function() { addRow(table, articleId); };

    const addColBtn = document.createElement('button');
    addColBtn.id = `table-${articleId}-add-col`;
    addColBtn.textContent = "Add Column";
    addColBtn.type = "button";
    addColBtn.onclick = function() { addCol(table, articleId); };

    const removeRowBtn = document.createElement('button');
    removeRowBtn.id = `table-${articleId}-remove-row`;
    removeRowBtn.textContent = "Remove Row";
    removeRowBtn.type = "button";
    removeRowBtn.onclick = function() { removeRow(table); };

    const removeColBtn = document.createElement('button');
    removeColBtn.id = `table-${articleId}-remove-col`;
    removeColBtn.textContent = "Remove Column";
    removeColBtn.type = "button";
    removeColBtn.onclick = function() { removeCol(table); };

    table.appendChild(addRowBtn);
    table.appendChild(addColBtn);
    table.appendChild(removeRowBtn);
    table.appendChild(removeColBtn);

    wrapper.prepend(table);

    const elementAddBtn = document.getElementById('add-element-btn');
    elementAddBtn.style.display = 'inline';
}

function addRow(generatedTable, articleId) {
    const rows = generatedTable.querySelectorAll(':scope > .table-row');
    const firstRow = rows[0];
    const numberOfColumns = firstRow.children.length;
    const row = rows.length;

    const rowDiv = document.createElement('div');
    rowDiv.style.display = 'flex';
    rowDiv.className = "table-row";
    for (let col = 0; col < numberOfColumns; col++) {
        const input = document.createElement('input');
        input.type = 'text';
        input.name = `table-${articleId}-cell-${row}-${col}`;
        input.id = `table-${articleId}-cell-${row}-${col}`;
        input.placeholder = `${row + 1},${col + 1}`;
        input.style.margin = '2px';
        rowDiv.appendChild(input);
    }

    const lastRow = rows[rows.length - 1];
    generatedTable.insertBefore(rowDiv, lastRow.nextSibling);
}

function removeRow(generatedTable) {
    const rows = generatedTable.querySelectorAll(':scope > .table-row');

    if (rows.length === 1) {
        generatedTable.parentNode.remove();
    }

    const lastRow = rows[rows.length - 1];
    lastRow.remove();
}

function addCol(generatedTable, articleId) {
    const rows = generatedTable.querySelectorAll(':scope > .table-row');
    const firstRow = rows[0];
    const numberOfColumns = firstRow.children.length;

    rowIndex = 0;
    rows.forEach(row => {
        const input = document.createElement('input');
        input.type = 'text';
        input.name = `table-${articleId}-cell-${rowIndex}-${numberOfColumns}`;
        input.id = `table-${articleId}-cell-${rowIndex}-${numberOfColumns}`;
        input.placeholder = `${rowIndex + 1},${numberOfColumns + 1}`;
        input.style.margin = '2px';
        row.appendChild(input);
        rowIndex++;
    });
}

function removeCol(generatedTable) {
    const rows = generatedTable.querySelectorAll(':scope > .table-row');
    const firstRow = rows[0];
    const numberOfColumns = firstRow.children.length;

    if (numberOfColumns === 1) {
        generatedTable.parentNode.remove();
    }

    rows.forEach((row) => {
        const lastColElement = row.children[row.children.length - 1];
        lastColElement.remove();
    });
}