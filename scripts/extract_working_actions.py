#!/usr/bin/env python3
"""
Extract all working action names from endpoint_reference.yaml
Create a simple list for ChatGPT custom instructions
"""

import yaml
import json

def extract_working_actions():
    with open('../output/endpoint_reference.yaml', 'r') as f:
        data = yaml.safe_load(f)

    working_actions = []

    for endpoint in data:
        if endpoint.get('status') == 'discovered':
            action = endpoint.get('operation_id')
            summary = endpoint.get('summary', '')
            params = endpoint.get('params', {})

            working_actions.append({
                'action': action,
                'summary': summary,
                'method': endpoint.get('method'),
                'requires_params': bool(params.get('query') or params.get('path') or params.get('body'))
            })

    # Sort by action name
    working_actions.sort(key=lambda x: x['action'])

    print(f"Found {len(working_actions)} working actions\n")

    # Create simple action list
    action_names = [a['action'] for a in working_actions]

    # Save as JSON
    with open('../output/working_actions.json', 'w') as f:
        json.dump(working_actions, f, indent=2)

    # Save simple list
    with open('../output/working_actions_list.txt', 'w') as f:
        for action in action_names:
            f.write(f"{action}\n")

    # Create ChatGPT custom instructions
    chatgpt_instructions = """When calling the MPS API, use ONLY these verified working actions:

"""

    # Group by category
    categories = {}
    for action_data in working_actions:
        action = action_data['action']
        category = action.split('/')[0] if '/' in action else 'General'
        if category not in categories:
            categories[category] = []
        categories[category].append(action)

    for category in sorted(categories.keys()):
        chatgpt_instructions += f"\n{category}:\n"
        for action in categories[category]:
            chatgpt_instructions += f"  - {action}\n"

    chatgpt_instructions += f"\n\nTotal: {len(working_actions)} working actions"
    chatgpt_instructions += "\n\nAll actions require dealer code NY06AGDWUQ (auto-populated)."
    chatgpt_instructions += "\nMost actions need no additional parameters."

    with open('../output/chatgpt_instructions.txt', 'w') as f:
        f.write(chatgpt_instructions)

    print("Created files:")
    print("  - output/working_actions.json (full details)")
    print("  - output/working_actions_list.txt (simple list)")
    print("  - output/chatgpt_instructions.txt (for ChatGPT)")
    print(f"\nTop 10 actions:")
    for action in action_names[:10]:
        print(f"  {action}")

if __name__ == '__main__':
    extract_working_actions()
