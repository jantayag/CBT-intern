class Pagination {
    constructor(options = {}) {
        this.containerId = options.containerId;
        this.items = options.items || [];
        
        this.itemsPerPage = options.itemsPerPage || 10;
        this.currentPage = 1;
        this.containerClass = options.containerClass || 'pagination-controls';
        this.renderItem = options.renderItem || this.defaultRenderItem;
        this.containerElement = null;
        this.contentContainer = null;
        
        this.displayConfig = {
            showingText: options.showingText || 'Showing',
            ofText: options.ofText || 'of',
            itemsText: options.itemsText || 'items',
            previousText: options.previousText || '« Previous',
            nextText: options.nextText || 'Next »',
            itemsPerPageText: options.itemsPerPageText || 'Items per page:',
            itemsPerPageOptions: options.itemsPerPageOptions || [5, 10, 20, 50]
        };

        this.init();
    }

    init() {
        this.contentContainer = document.getElementById(this.containerId);
        if (!this.contentContainer) {
            throw new Error(`Container with id '${this.containerId}' not found`);
        }

        this.render();
    }

    defaultRenderItem(item) {
        if (item instanceof HTMLElement) {
            return item.cloneNode(true);
        }
        const div = document.createElement('div');
        div.textContent = typeof item === 'object' ? JSON.stringify(item) : String(item);
        return div;
    }

    createPaginationControls() {
        const totalPages = Math.ceil(this.items.length / this.itemsPerPage);
        const container = document.createElement('div');
        container.className = this.containerClass;
        
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = Math.min(startIndex + this.itemsPerPage, this.items.length);
        
        container.innerHTML = `
            <div class="pagination-info">
                ${this.displayConfig.showingText} <span id="showing-start">${this.items.length > 0 ? startIndex + 1 : 0}</span> -
                <span id="showing-end">${endIndex}</span> ${this.displayConfig.ofText} 
                <span id="total-items">${this.items.length}</span> ${this.displayConfig.itemsText}
            </div>
            <div class="pagination-buttons">
                <button class="pagination-btn" id="prev-page" ${this.currentPage === 1 ? 'disabled' : ''}>
                    ${this.displayConfig.previousText}
                </button>
                <div class="page-numbers">
                    ${this.generatePageNumbers(totalPages)}
                </div>
                <button class="pagination-btn" id="next-page" ${this.currentPage === totalPages ? 'disabled' : ''}>
                    ${this.displayConfig.nextText}
                </button>
            </div>
            <div class="items-per-page">
                <label for="items-per-page">${this.displayConfig.itemsPerPageText}</label>
                <select id="items-per-page">
                    ${this.displayConfig.itemsPerPageOptions.map(num => 
                        `<option value="${num}" ${num === this.itemsPerPage ? 'selected' : ''}>${num}</option>`
                    ).join('')}
                </select>
            </div>`;

        return container;
    }

    generatePageNumbers(totalPages) {
        if (this.containerId === 'students-table-container') {
            return this.generateModalPageNumbers(totalPages);
        }

        let pageNumbers = '';
        for (let i = 1; i <= totalPages; i++) {
            pageNumbers += `
                <button class="page-number ${i === this.currentPage ? 'active' : ''}" 
                        data-page="${i}">
                    ${i}
                </button>`;
        }
        return pageNumbers;
    }

    generateModalPageNumbers(totalPages) {
        let pageNumbers = '';

        pageNumbers += `
            <button class="page-number ${1 === this.currentPage ? 'active' : ''}" 
                    data-page="1">
                1
            </button>`;

        if (this.currentPage > 3) {
            pageNumbers += '<span class="ellipsis">...</span>';
        }

        if (this.currentPage !== 1 && this.currentPage !== totalPages) {
            pageNumbers += `
                <button class="page-number active" 
                        data-page="${this.currentPage}">
                    ${this.currentPage}
                </button>`;
        }

        if (this.currentPage < totalPages - 2) {
            pageNumbers += '<span class="ellipsis">...</span>';
        }

        if (totalPages > 1) {
            pageNumbers += `
                <button class="page-number ${totalPages === this.currentPage ? 'active' : ''}" 
                        data-page="${totalPages}">
                    ${totalPages}
                </button>`;
        }

        return pageNumbers;
    }

    createPaginationControls() {
        const totalPages = Math.ceil(this.items.length / this.itemsPerPage);
        const container = document.createElement('div');
        container.className = this.containerClass;
        
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = Math.min(startIndex + this.itemsPerPage, this.items.length);

        if (this.containerId === 'students-table-container') {
            container.innerHTML = `
                <div class="pagination-buttons">
                    <button class="pagination-btn" id="prev-page" ${this.currentPage === 1 ? 'disabled' : ''}>
                        ${this.displayConfig.previousText}
                    </button>
                    <div class="page-numbers">
                        ${this.generatePageNumbers(totalPages)}
                    </div>
                    <button class="pagination-btn" id="next-page" ${this.currentPage === totalPages ? 'disabled' : ''}>
                        ${this.displayConfig.nextText}
                    </button>
                </div>
                <div class="items-per-page">
                    <select id="items-per-page">
                        ${this.displayConfig.itemsPerPageOptions.map(num => 
                            `<option value="${num}" ${num === this.itemsPerPage ? 'selected' : ''}>${num}</option>`
                        ).join('')}
                    </select>
                </div>`;
        } else {
            container.innerHTML = `
                <div class="pagination-info">
                    ${this.displayConfig.showingText} <span id="showing-start">${this.items.length > 0 ? startIndex + 1 : 0}</span> -
                    <span id="showing-end">${endIndex}</span> ${this.displayConfig.ofText} 
                    <span id="total-items">${this.items.length}</span> ${this.displayConfig.itemsText}
                </div>
                <div class="pagination-buttons">
                    <button class="pagination-btn" id="prev-page" ${this.currentPage === 1 ? 'disabled' : ''}>
                        ${this.displayConfig.previousText}
                    </button>
                    <div class="page-numbers">
                        ${this.generatePageNumbers(totalPages)}
                    </div>
                    <button class="pagination-btn" id="next-page" ${this.currentPage === totalPages ? 'disabled' : ''}>
                        ${this.displayConfig.nextText}
                    </button>
                </div>
                <div class="items-per-page">
                    <label for="items-per-page">${this.displayConfig.itemsPerPageText}</label>
                    <select id="items-per-page">
                        ${this.displayConfig.itemsPerPageOptions.map(num => 
                            `<option value="${num}" ${num === this.itemsPerPage ? 'selected' : ''}>${num}</option>`
                        ).join('')}
                    </select>
                </div>`;
        }

        return container;
    }

    setupEventListeners() {
        this.containerElement.querySelectorAll('.page-number').forEach(button => {
            button.addEventListener('click', (e) => {
                const pageNum = parseInt(e.target.dataset.page);
                this.goToPage(pageNum);
            });
        });

        const prevButton = this.containerElement.querySelector('#prev-page');
        prevButton?.addEventListener('click', () => {
            if (this.currentPage > 1) {
                this.goToPage(this.currentPage - 1);
            }
        });

        const nextButton = this.containerElement.querySelector('#next-page');
        nextButton?.addEventListener('click', () => {
            const totalPages = Math.ceil(this.items.length / this.itemsPerPage);
            if (this.currentPage < totalPages) {
                this.goToPage(this.currentPage + 1);
            }
        });

        const itemsPerPageSelect = this.containerElement.querySelector('#items-per-page');
        itemsPerPageSelect?.addEventListener('change', (e) => {
            this.itemsPerPage = parseInt(e.target.value);
            this.currentPage = 1;
            this.render();
        });
    }

    goToPage(pageNumber) {
        this.currentPage = pageNumber;
        this.render();
    }

    renderPageItems() {
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = Math.min(startIndex + this.itemsPerPage, this.items.length);
        
        if (this.items[0]?.tagName === 'TR') {
            const table = document.createElement('table');
            if (this.tableId) table.id = this.tableId;
            if (this.tableClass) table.className = this.tableClass;
            
            const originalTable = this.items[0].closest('table');
            if (originalTable) {
                const originalHeader = originalTable.querySelector('thead');
                if (originalHeader) {
                    const thead = originalHeader.cloneNode(true);
                    table.appendChild(thead);
                }
            }
            
            const tbody = document.createElement('tbody');
            
            for (let i = startIndex; i < endIndex; i++) {
                const renderedItem = this.renderItem(this.items[i]);
                tbody.appendChild(renderedItem);
            }
            
            table.appendChild(tbody);
            
            if (this.tableClass?.includes('table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                wrapper.appendChild(table);
                return wrapper;
            }
            
            return table;
        } 
        else {
            const container = document.createElement('div');
            container.className = 'pagination-items';
            
            for (let i = startIndex; i < endIndex; i++) {
                const renderedItem = this.renderItem(this.items[i]);
                container.appendChild(renderedItem);
            }
            
            return container;
        }
    }

    render() {
        this.contentContainer.innerHTML = '';
        
        const contentWrapper = this.renderPageItems();
        this.contentContainer.appendChild(contentWrapper);
        
        if (this.containerElement) {
            this.containerElement.remove();
        }
        
        this.containerElement = this.createPaginationControls();
        this.contentContainer.appendChild(this.containerElement);
        
        this.setupEventListeners();
    }

    updateItems(newItems) {
        this.items = newItems;
        this.currentPage = 1;
        this.render();
    }

    updateItemsPerPage(newItemsPerPage) {
        this.itemsPerPage = newItemsPerPage;
        this.currentPage = 1;
        this.render();
    }

    getCurrentPage() {
        return this.currentPage;
    }

    getTotalPages() {
        return Math.ceil(this.items.length / this.itemsPerPage);
    }
}


