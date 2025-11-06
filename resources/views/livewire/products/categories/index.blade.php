<div>
    <div class="min-h-screen bg-gray-50">
        <x-categories.breadcrumbs :items="$breadcrumbs" />
        
        <div class="container mx-auto px-4 py-8">
            <div class="grid md:grid-cols-[300px_1fr] gap-8">
                <!-- Left: Filter Sidebar -->
                <div>
                    <x-categories.filter-sidebar />
                </div>
                
                <!-- Right: Category Grid -->
                <div>
                    <x-categories.category-grid />
                </div>
            </div>
        </div>
    </div>
</div>
