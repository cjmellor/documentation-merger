# Documentation Merger

## What is this?

A console command to allow you to select repository for programming documentation and merge it all into one file.

## Why would I want this?

One more recent example is utilising it with [OpenAI Assistants](https://platform.openai.com/docs/assistants/overview) where you can supply an Assistant with data to use when prompting. In this case, the file is the frameworks' entire documentation. So as soon
as your favourite framework brings out a new feature, you can retrieve the documentation, and upload it to your Assistant and now you can get help for developing with the new features, without having to wait until OpenAI update their data cutoff.

## Usage

Go to `app/Console/Commands/MergeDocs.php` and update the `$repoDocs` and `$pathToDocs` arrays to match the repository URLs and paths you want to use.

> **Note**
> 
> For starters, I have added the Tailwind CSS, AlpineJS, Livewire and Laravel repositories (the TALL stack). You can remove these and add your own.

Call the command:

```bash
php artisan app:merge-docs
```

You'll be presented with the framework options (see above on how to amend these). Select the framework you want to merge the docs for, and the command will do the rest.

The merged documentation files are stored in `storage/app/docs`.

## Contributing

It is a simple command, but if you see any use cases to further expand on this, please feel free to submit a PR.


