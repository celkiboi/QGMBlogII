document.addEventListener('DOMContentLoaded', () => {
    const addButton = document.getElementById('add-element-btn');
    const chooser = document.getElementById('element-chooser');
    const form = document.getElementById('article-form');
    const body = document.getElementById('article-body');
    const submitPublishedBtn = document.getElementById('submit-article-published-btn');
    const submitUnpublishedBtn = document.getElementById('submit-article-unpublished-btn');
    const apiEndPoint = document.querySelector('input[name="api-endpoint"]').value;

    let blockCounter = 0;

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
